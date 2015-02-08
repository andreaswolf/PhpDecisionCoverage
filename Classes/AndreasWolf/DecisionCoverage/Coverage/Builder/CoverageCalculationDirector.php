<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\CoverageBuildProgressReporter;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageDataSetEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\SampleEvent;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightBuilder;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use AndreasWolf\DecisionCoverage\Source\RecursiveSyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\SyntaxTreeStack;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageCalculationDirector {

	/**
	 * @var CoverageSet
	 */
	protected $coverageSet;

	/**
	 * @var CoverageBuilderFactory
	 */
	protected $builderFactory;

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;

	/**
	 * @var LoggerInterface
	 */
	protected $log;


	/**
	 * @param CoverageSet $coverageSet The coverage data set to insert the generated coverage into
	 * @param OutputInterface $output
	 * @param CoverageBuilderFactory $factory
	 * @param ExpressionService $expressionService
	 * @param EventDispatcherInterface $eventDispatcher The event dispatcher to use. Optional because the events used
	 *   here don't necessarily need to be handled globally
	 * @param LoggerInterface $log
	 */
	public function __construct(CoverageSet $coverageSet, OutputInterface $output,
	                            CoverageBuilderFactory $factory = NULL, ExpressionService $expressionService = NULL,
	                            EventDispatcherInterface $eventDispatcher = NULL, LoggerInterface $log = NULL) {
		if (!$eventDispatcher) {
			$eventDispatcher = new EventDispatcher();
		}
		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}
		if (!$log) {
			$log = new NullLogger();
		}
		if (!$factory) {
			$factory = new CoverageBuilderFactory($eventDispatcher,
				new CoverageFactory($expressionService, new DecisionInputBuilder($log)), $log);
		}

		$this->coverageSet = $coverageSet;
		$this->builderFactory = $factory;
		$this->eventDispatcher = $eventDispatcher;
		$this->expressionService = $expressionService;
		$this->log = $log;

		if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
			$this->eventDispatcher->addSubscriber(new CoverageBuildProgressReporter($output));
		}
	}

	/**
	 */
	public function build() {
		$dataSet = $this->coverageSet->getDataSet();
		$this->createCoverageBuildersForDataSet($dataSet);
		$this->eventDispatcher->dispatch('coverage.build.start', new CoverageDataSetEvent($dataSet));

		$samples = $dataSet->getSamples();
		foreach ($samples as $dataSample) {
			$this->eventDispatcher->dispatch('coverage.sample.received', new SampleEvent($dataSample));
		}

		$this->eventDispatcher->dispatch('coverage.build.end', new CoverageDataSetEvent($dataSet));
	}

	/**
	 */
	protected function createCoverageBuildersForDataSet() {
		$treeStack = new SyntaxTreeStack($this->eventDispatcher);
		$this->eventDispatcher->addSubscriber($treeStack);

		foreach ($this->coverageSet->getAnalysisResult()->getFileResults() as $potentiallyCoveredFile) {
			$this->createFileCoverageBuilders($potentiallyCoveredFile);
		}
	}

	/**
	 * @param FileResult $file
	 */
	protected function createFileCoverageBuilders(FileResult $file) {
		$this->log->debug('Starting to create builders for file ' . $file->getFilePath());

		$fileCoverage = new FileCoverage($file->getFilePath());
		$this->coverageSet->add($fileCoverage);

		foreach ($this->createFileIterator($file) as $syntaxTreeNode) {
			if (!$syntaxTreeNode instanceof Node\Stmt) {
				continue;
			}

			// TODO use an event-based approach for generating the coverage builders instead

			if ($syntaxTreeNode instanceof Node\Stmt\ClassMethod) {
				// TODO this will break if there is code outside the class after the first method
				$currentMethodCoverage = new MethodCoverage($syntaxTreeNode->name, $syntaxTreeNode->getAttribute('coverage__nodeId'));

				$fileCoverage->addCoverage($currentMethodCoverage);

				$this->createMethodEntryCoverageBuilder($currentMethodCoverage, $syntaxTreeNode);
			}

			if ($syntaxTreeNode instanceof Node\Stmt\If_) {
				$this->buildExpressionWeights($syntaxTreeNode->cond);
				$weight = $syntaxTreeNode->cond->getAttribute('coverage__weight');
				$this->log->debug('Encountered if statement with expression weights ' . $weight->getTrueValue() . '/' . $weight->getFalseValue());

				$builder = $this->createBuilderForNode($syntaxTreeNode->cond);
				if (isset($currentMethodCoverage)) {
					$currentMethodCoverage->addCoverage($builder->getCoverage());
				} else {
					$fileCoverage->addCoverage($builder->getCoverage());
				}
			}
		}
	}

	protected function createMethodEntryCoverageBuilder(MethodCoverage $methodCoverage, Node\Stmt\ClassMethod $methodNode) {
		if (count($methodNode->stmts) == 0) {
			return;
		}

		$entryBuilder = new MethodEntryCoverageBuilder($methodCoverage, $this->log);
		$this->eventDispatcher->addSubscriber($entryBuilder);
		$this->log->debug('Added coverage builder for method entry of method ' . $methodNode->name);
	}

	/**
	 * Builds weights for an expression and all its sub-expressions.
	 *
	 * @param Node\Expr $expression
	 */
	protected function buildExpressionWeights(Node\Expr $expression) {
		$builder = new ExpressionWeightBuilder();
		$builder->buildForExpression($expression);
	}

	/**
	 * @param Node $node
	 * @return SingleConditionCoverageBuilder
	 */
	protected function createBuilderForNode(Node $node) {
		$builder = $this->builderFactory->createBuilderForExpression($node);

		return $builder;
	}

	/**
	 * @param FileResult $file
	 * @return RecursiveSyntaxTreeIterator
	 */
	protected function createFileIterator(FileResult $file) {
		return new RecursiveSyntaxTreeIterator($file->getSyntaxTree()->getIterator(), $this->eventDispatcher,
			\RecursiveIteratorIterator::SELF_FIRST
		);
	}

}
 
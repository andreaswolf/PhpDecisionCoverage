<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\Coverage\Event\DataSampleEvent;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightBuilder;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use AndreasWolf\DecisionCoverage\Source\RecursiveSyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\SyntaxTreeStack;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageCalculationDirector {

	/**
	 * @var Probe[]
	 */
	protected $knownProbes = array();

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
	 * @param CoverageBuilderFactory $factory
	 * @param ExpressionService $expressionService
	 * @param EventDispatcherInterface $eventDispatcher The event dispatcher to use. Optional because the events used
	 *   here don't necessarily need to be handled globally
	 * @param LoggerInterface $log
	 */
	public function __construct(CoverageSet $coverageSet, CoverageBuilderFactory $factory = NULL,
	                            ExpressionService $expressionService = NULL,
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
				new CoverageFactory($expressionService, $eventDispatcher), $log);
		}

		$this->coverageSet = $coverageSet;
		$this->builderFactory = $factory;
		$this->eventDispatcher = $eventDispatcher;
		$this->expressionService = $expressionService;
		$this->log = $log;
	}

	/**
	 */
	public function build() {
		$this->createCoverageBuildersForDataSet($this->coverageSet->getDataSet());

		foreach ($this->coverageSet->getDataSet()->getSamples() as $dataSample) {
			$this->eventDispatcher->dispatch('coverage.sample.received', new DataSampleEvent($dataSample));
		}
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

			if ($syntaxTreeNode instanceof Node\Stmt\If_) {
				$this->buildExpressionWeights($syntaxTreeNode->cond);
				$weight = $syntaxTreeNode->cond->getAttribute('coverage__weight');
				$this->log->debug('Encountered if statement with expression weights ' . $weight->getTrueValue() . '/' . $weight->getFalseValue());

				$builder = $this->createBuilderForNode($syntaxTreeNode->cond);
				$fileCoverage->addCoverage($builder->getCoverage());
			}
		}
	}

	/**
	 * Makes sure that
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
		return new RecursiveSyntaxTreeIterator($file->getSyntaxTree()->getIterator(), $this->eventDispatcher);
	}

}
 
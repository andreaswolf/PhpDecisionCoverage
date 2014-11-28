<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use AndreasWolf\DecisionCoverage\Source\RecursiveSyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * @author Andreas Wolf <aw@foundata.net>
 *
 * TODO introduce a logger
 */
class CoverageCalculationDirector {

	/**
	 * @var Probe[]
	 */
	protected $knownProbes = array();

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
	 * @param CoverageBuilderFactory $factory
	 * @param ExpressionService $expressionService
	 * @param EventDispatcherInterface $eventDispatcher The event dispatcher to use. Optional because the events used
	 *   here don't necessarily need to be handled globally
	 * @param LoggerInterface $log
	 */
	public function __construct(CoverageBuilderFactory $factory = NULL, ExpressionService $expressionService = NULL,
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

		$this->builderFactory = $factory;
		$this->eventDispatcher = $eventDispatcher;
		$this->expressionService = $expressionService;
		$this->log = $log;
	}

	/**
	 * @param CoverageDataSet $dataSet
	 */
	public function buildForDataSet(CoverageDataSet $dataSet) {
		$this->createCoverageBuildersForDataSet($dataSet);

		// TODO create data set for coverage
	}

	/**
	 * @param CoverageDataSet $dataSet
	 */
	protected function createCoverageBuildersForDataSet(CoverageDataSet $dataSet) {
		foreach ($dataSet->getAnalysisResult()->getFileResults() as $potentiallyCoveredFile) {
			$this->createFileCoverageBuilders($potentiallyCoveredFile, $dataSet);
		}
	}

	/**
	 * @param FileResult $file
	 * @param CoverageDataSet $dataSet
	 */
	protected function createFileCoverageBuilders(FileResult $file, CoverageDataSet $dataSet) {
		foreach ($this->createFileIterator($file) as $syntaxTreeNode) {
			if (!$syntaxTreeNode instanceof Node\Stmt) {
				continue;
			}

			if ($syntaxTreeNode instanceof Node\Stmt\If_) {
				$this->createBuilderForNode($syntaxTreeNode->cond);
			}
		}
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
 
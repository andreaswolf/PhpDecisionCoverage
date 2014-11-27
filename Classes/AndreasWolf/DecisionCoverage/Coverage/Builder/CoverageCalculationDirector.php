<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
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


	public function __construct(CoverageBuilderFactory $factory = NULL, ExpressionService $expressionService = NULL,
	                            EventDispatcherInterface $eventDispatcher = NULL) {
		if (!$eventDispatcher) {
			$eventDispatcher = new EventDispatcher();
		}
		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}
		if (!$factory) {
			$factory = new CoverageBuilderFactory($eventDispatcher, new CoverageFactory($expressionService, $eventDispatcher));
		}

		$this->builderFactory = $factory;
		$this->eventDispatcher = $eventDispatcher;
		$this->expressionService = $expressionService;
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
		foreach ($file->getSyntaxTree()->getIterator() as $syntaxTreeNode) {
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

}
 
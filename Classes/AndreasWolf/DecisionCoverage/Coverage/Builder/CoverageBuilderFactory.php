<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use PhpParser\Node\Expr;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class CoverageBuilderFactory {

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var LoggerInterface
	 */
	protected $log;

	/**
	 * @var CoverageFactory
	 */
	protected $coverageFactory;

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;


	public function __construct(EventDispatcherInterface $dispatcher, CoverageFactory $coverageFactory, LoggerInterface $log = NULL) {
		$this->eventDispatcher = $dispatcher;
		$this->log = $log;

		$this->coverageFactory = $coverageFactory;
		$this->expressionService = new ExpressionService();
	}

	/**
	 * Generic factory method for coverage builders.
	 *
	 * This might internally create a hierarchy of builders; return value will only be the top-level builder.
	 *
	 * @param Expr $expression
	 * @return CoverageBuilder
	 */
	public function createBuilderForExpression(Expr $expression) {
		if ($this->expressionService->isDecisionExpression($expression)) {
			return $this->createBuilderForDecision($expression);
		} else {
			return $this->createBuilderForCondition($expression);
		}
	}

	/**
	 * Creates a builder structure for a decision node.
	 *
	 * The builder is attached to the event dispatcher.
	 *
	 * @param Expr\BinaryOp $decision
	 * @return DecisionCoverageBuilder
	 */
	public function createBuilderForDecision(Expr\BinaryOp $decision) {
		$partialCoverageBuilders = $partialCoverages = array();
		foreach (array($decision->left, $decision->right) as $partialExpression) {
			// the partial expression might also be a decision (e.g. in "A && (B || C)")
			$builder = $this->createBuilderForExpression($partialExpression);
			$partialCoverageBuilders[] = $builder;
			$partialCoverages[] = $builder->getCoverage();
		}
		$decisionCoverage = $this->coverageFactory->createCoverageForDecision($decision, $partialCoverages);
		$coverageBuilder = new DecisionCoverageBuilder($decisionCoverage, $partialCoverageBuilders,
			$this->eventDispatcher, $this->log);
		$this->eventDispatcher->addSubscriber($coverageBuilder);

		return $coverageBuilder;
	}

	/**
	 * @param Expr $expression
	 * @return SingleConditionCoverageBuilder
	 */
	public function createBuilderForCondition(Expr $expression) {
		$coverage = $this->coverageFactory->createCoverageForNode($expression);
		$builder = new SingleConditionCoverageBuilder($expression, $coverage, $this->eventDispatcher, $this->log);
		$this->eventDispatcher->addSubscriber($builder);

		return $builder;
	}

}

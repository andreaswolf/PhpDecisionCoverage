<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use PhpParser\Node\Expr;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class CoverageBuilderFactory {

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var CoverageFactory
	 */
	protected $coverageFactory;

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;


	public function __construct(EventDispatcherInterface $dispatcher, CoverageFactory $coverageFactory) {
		$this->eventDispatcher = $dispatcher;
		$this->coverageFactory = $coverageFactory;
		$this->expressionService = new ExpressionService();
	}

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
	 * The builder is attached as a listener to the event dispatcher.
	 *
	 * @param Expr\BinaryOp $decision
	 * @return DecisionCoverageBuilder
	 */
	public function createBuilderForDecision(Expr\BinaryOp $decision) {
		$partialCoverageBuilders = array();
		foreach (array($decision->left, $decision->right) as $partialExpression) {
			// the partial expression might also be a decision (e.g. in "A && (B || C)")
			$partialCoverageBuilders[] = $this->createBuilderForExpression($partialExpression);
		}
		$decisionCoverage = $this->coverageFactory->createCoverageForNode($decision);
		$coverageBuilder = new DecisionCoverageBuilder($partialCoverageBuilders);
		$this->eventDispatcher->addSubscriber($coverageBuilder);

		return $coverageBuilder;
	}

	/**
	 * @param Expr $expression
	 * @return SingleConditionCoverageBuilder
	 */
	public function createBuilderForCondition(Expr $expression) {
		$coverage = $this->coverageFactory->createCoverageForNode($expression);
		$builder = new SingleConditionCoverageBuilder($expression, $coverage);
		$this->eventDispatcher->addSubscriber($builder);

		return $builder;
	}

}

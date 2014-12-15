<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use PhpParser\Node\Expr;


/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageFactory {

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;

	/**
	 * @var CoverageBuilderFactory
	 */
	protected $coverageBuilderFactory;

	/**
	 * @var DecisionInputBuilder
	 */
	protected $decisionInputBuilder;


	public function __construct(ExpressionService $expressionService = NULL,
	                            DecisionInputBuilder $decisionInputBuilder = NULL) {
		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}
		if (!$decisionInputBuilder) {
			$decisionInputBuilder = new DecisionInputBuilder();
		}

		$this->expressionService = $expressionService;
		$this->decisionInputBuilder = $decisionInputBuilder;
	}

	public function canCreateCoverage(Expr $node) {
		return ($node instanceof Expr\BinaryOp);
	}

	/**
	 * @param Expr $node
	 * @return Coverage
	 */
	public function createCoverageForNode(Expr $node) {
		if ($this->expressionService->isDecisionExpression($node)) {
			throw new \InvalidArgumentException('createCoverageForNode() cannot create coverage for decision; use method createCoverageForDecision() instead.');
		} else {
			return new SingleConditionCoverage($node);
		}
	}

	/**
	 * Creates a coverage object for the given decision statement.
	 *
	 * Using the decision input builder, this method also gets the feasible inputs for the decision.
	 *
	 * @param Expr\BinaryOp $node
	 * @return DecisionCoverage
	 */
	public function createCoverageForDecision(Expr\BinaryOp $node) {
		$decisionInputs = $this->decisionInputBuilder->buildInput($node);

		return new DecisionCoverage($node, $decisionInputs);
	}

}
 
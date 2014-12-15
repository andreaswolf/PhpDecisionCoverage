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


	public function __construct(ExpressionService $expressionService = NULL) {
		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}

		$this->expressionService = $expressionService;
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
	 * @param Expr $node
	 * @param Coverage[] $partialCoverages
	 * @return DecisionCoverage
	 */
	public function createCoverageForDecision(Expr $node, $partialCoverages) {
		return new DecisionCoverage($node, $partialCoverages);
	}

}
 
<?php
namespace AndreasWolf\DecisionCoverage\Coverage\MCDC;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\ExpressionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use PhpParser\Node\Expr;


class DecisionCoverage extends ExpressionCoverage {

	/**
	 * @var Expr
	 */
	protected $expression;

	/**
	 * @var SingleConditionCoverage[]
	 */
	protected $conditionCoverages;


	/**
	 * @param Expr $expression The covered expression
	 * @param SingleConditionCoverage[] $conditionCoverages The coverages of the conditions this decision is comprised of
	 */
	public function __construct(Expr $expression, $conditionCoverages) {
		parent::__construct($expression);
		$this->conditionCoverages = $conditionCoverages;
	}

	/**
	 * @return float The coverage as a value between 0 and 1.
	 */
	public function getCoverage() {
		// TODO: Implement getCoverage() method.
	}

}

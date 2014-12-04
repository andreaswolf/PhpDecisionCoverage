<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Weighting;

use PhpParser\Node\Expr;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ExpressionWeightFactory {

	/**
	 * @param Expr $expr
	 * @return ExpressionWeight
	 */
	public function createForCondition(Expr $expr) {
		return new ConditionWeight();
	}

}

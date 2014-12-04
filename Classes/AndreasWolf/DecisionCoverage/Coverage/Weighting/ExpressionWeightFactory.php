<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Weighting;

use PhpParser\Node\Expr;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ExpressionWeightFactory {

	protected $decisionTypeToWeightClassMap = array(
		'Expr_BinaryOp_BooleanAnd' => 'BooleanAndWeight',
		'Expr_BinaryOp_BooleanOr' => 'BooleanOrWeight',
	);

	/**
	 * @param Expr $expr
	 * @return ConditionWeight
	 */
	public function createForCondition(Expr $expr) {
		return new ConditionWeight();
	}

	/**
	 * @param Expr\BinaryOp $expr
	 * @param ExpressionWeight $leftWeight
	 * @param ExpressionWeight $rightWeight
	 * @return DecisionWeight
	 */
	public function createForDecision(Expr\BinaryOp $expr, ExpressionWeight $leftWeight, ExpressionWeight $rightWeight) {
		if (!array_key_exists($expr->getType(), $this->decisionTypeToWeightClassMap)) {
			throw new \InvalidArgumentException('No decision weighting available for type ' . $expr->getType(), 1417689417);
		}
		$type = 'AndreasWolf\\DecisionCoverage\\Coverage\\Weighting\\'
			. $this->decisionTypeToWeightClassMap[$expr->getType()];

		/** @var DecisionWeight $decisionWeight */
		$decisionWeight = new $type($leftWeight, $rightWeight);

		return $decisionWeight;
	}

}

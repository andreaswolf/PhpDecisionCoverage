<?php
namespace AndreasWolf\DecisionCoverage\Service;


use PhpParser\Node\Expr;


class ExpressionService {

	/**
	 * The types of relational operators
	 *
	 * @var array
	 */
	protected $relationalOperatorTypes = array(
		'Expr_BinaryOp_Equal', 'Expr_BinaryOp_Identical',
		'Expr_BinaryOp_Greater', 'Expr_BinaryOp_GreaterOrEqual',
		'Expr_BinaryOp_Smaller', 'Expr_BinaryOp_SmallerOrEqual',
	);


	/**
	 * Returns TRUE if the given expression is a decision, i.e. comprised of more than one condition
	 *
	 * @param Expr $expr
	 * @return bool
	 */
	public function isDecisionExpression(Expr $expr) {
		// TODO implement check for BooleanNot => peek into it to see if it contains a decision
		return in_array(
			$expr->getType(),
			array('Expr_BinaryOp_LogicalAnd', 'Expr_BinaryOp_LogicalOr', 'Expr_BinaryOp_LogicalXor',
				/*&&*/'Expr_BinaryOp_BooleanAnd', /*||*/'Expr_BinaryOp_BooleanOr',
			)
		);
	}

	/**
	 * Returns TRUE if the given expression expresses a relation.
	 *
	 * @param Expr $expr
	 * @return bool
	 */
	public function isRelationalExpression(Expr $expr) {
		return in_array($expr->getType(), $this->relationalOperatorTypes);
	}

}

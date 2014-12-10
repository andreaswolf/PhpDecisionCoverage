<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Coupling;


use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\LNumber;


/**
 *
 *
 * This class uses a few definitions for its work:
 *
 * - "similar" relations are: > and >=, < and <=
 *
 * @author Andreas Wolf <aw@foundata.net>
 *
 * TODO make this work independent of the ordering within a condition, i.e. $a < 5 and 5 > $a should be treated equal
 */
class CouplingClassifier {

	protected $similarTypes = array(
		array('Expr_BinaryOp_Greater', 'Expr_BinaryOp_GreaterOrEqual'),
		array('Expr_BinaryOp_Smaller', 'Expr_BinaryOp_SmallerOrEqual')
	);

	public function determineConditionCoupling(Expr\BinaryOp $leftExpression, Expr\BinaryOp $rightExpression) {
		if (!$this->doReferenceSameVariable($leftExpression, $rightExpression)) {
			return ConditionCoupling::TYPE_UNCOUPLED;
		} else {
			if ($leftExpression->getType() == $rightExpression->getType()) {
				return $this->determineCouplingOfSameTypeConditions($leftExpression, $rightExpression);
			} elseif ($this->haveSimilarTypes($leftExpression, $rightExpression)) {
				return $this->determineCouplingOfSimilarTypeConditions($leftExpression, $rightExpression);
			}
		}
		return NULL;
	}

	/**
	 * Tests if expressions
	 *
	 * @param Expr\BinaryOp $leftExpression
	 * @param Expr\BinaryOp $rightExpression
	 * @return bool
	 */
	protected function doReferenceSameVariable(Expr\BinaryOp $leftExpression, Expr\BinaryOp $rightExpression) {
		$leftVariable = $leftExpression->left instanceof Expr\Variable ? $leftExpression->left : $leftExpression->right;
		$rightVariable = $rightExpression->left instanceof Expr\Variable ? $rightExpression->left : $rightExpression->right;

		return ($leftVariable->name === $rightVariable->name);
	}

	protected function determineCouplingOfSameTypeConditions(Expr\BinaryOp $leftExpression,
	                                                         Expr\BinaryOp $rightExpression) {
		$leftValue = $this->extractConstantValue($leftExpression->right);
		$rightValue = $this->extractConstantValue($rightExpression->right);

		// same variable and same value means the conditions are effectively the same
		if ($leftValue == $rightValue) {
			return ConditionCoupling::TYPE_IDENTICAL;
		}

		switch ($leftExpression->getType()) {
			case 'Expr_BinaryOp_Greater':
			case 'Expr_BinaryOp_GreaterOrEqual':
				if ($leftValue < $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				} elseif ($leftValue > $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				} // equal values were handled above
				break;

			case 'Expr_BinaryOp_Smaller':
			case 'Expr_BinaryOp_SmallerOrEqual':
				if ($leftValue > $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				} elseif ($leftValue < $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				} // equal values were handled above
		}
		return NULL; // this should never happen
	}

	protected function determineCouplingOfSimilarTypeConditions(Expr\BinaryOp $leftExpression,
	                                                            Expr\BinaryOp $rightExpression) {
		$leftValue = $this->extractConstantValue($leftExpression->right);
		$rightValue = $this->extractConstantValue($rightExpression->right);

		switch ($leftExpression->getType()) {
			case 'Expr_BinaryOp_GreaterOrEqual':
				// the right expression is a "greater"
				if ($leftValue == $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				}

				break;

			case 'Expr_BinaryOp_Greater':
				// the right expression is a "greater or equal"
				if ($leftValue == $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				}

				break;

			case 'Expr_BinaryOp_SmallerOrEqual':
				// the right expression is a "smaller"
				if ($leftValue == $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				}

				break;

			case 'Expr_BinaryOp_Smaller':
				// the right expression is a "smaller or equal"
				if ($leftValue == $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				}

				break;
		}
		return NULL; // this should never happen
	}

	protected function haveSimilarTypes(Expr\BinaryOp $left, Expr\BinaryOp $right) {
		return
			(in_array($left->getType(), $this->similarTypes[0]) && in_array($right->getType(), $this->similarTypes[0]))
			|| (in_array($left->getType(), $this->similarTypes[1]) && in_array($right->getType(), $this->similarTypes[1]));
	}

	/**
	 * @param Expr $constantExpression
	 * @return Expr
	 */
	protected function extractConstantValue(Expr $constantExpression) {
		if ($constantExpression instanceof LNumber) {
			return $constantExpression->value;
		}
	}

}

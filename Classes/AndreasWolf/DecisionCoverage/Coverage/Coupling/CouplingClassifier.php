<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Coupling;

use AndreasWolf\DecisionCoverage\Coverage\Comparison;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\LNumber;


/**
 *
 *
 * This class uses a few definitions for its work:
 *
 * - "similar" relations are: > and >=, < and <=
 * - "contrary" relations are >/>= and </<=
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class CouplingClassifier {

	protected $similarTypes = array(
		array('Expr_BinaryOp_Greater', 'Expr_BinaryOp_GreaterOrEqual'),
		array('Expr_BinaryOp_Smaller', 'Expr_BinaryOp_SmallerOrEqual')
	);

	public function determineConditionCoupling(Expr\BinaryOp $leftExpression, Expr\BinaryOp $rightExpression) {
		$leftExpression = Comparison::canonicalize($leftExpression);
		$rightExpression = Comparison::canonicalize($rightExpression);

		if (!$this->doReferenceSameVariable($leftExpression, $rightExpression)) {
			return ConditionCoupling::TYPE_UNCOUPLED;
		} else {
			if ($leftExpression->getType() == $rightExpression->getType()) {
				return $this->determineCouplingOfSameTypeConditions($leftExpression, $rightExpression);
			} elseif ($this->haveSimilarTypes($leftExpression, $rightExpression)) {
				return $this->determineCouplingOfSimilarTypeConditions($leftExpression, $rightExpression);
			} elseif ($leftExpression instanceof Expr\BinaryOp\Equal || $rightExpression instanceof Expr\BinaryOp\Equal) {
				return $this->determineCouplingOfExpressionsConditionsWithEqualOnOneSide($leftExpression, $rightExpression);
			} else {
				return $this->determineCouplingOfContraryTypeConditions($leftExpression, $rightExpression);
			}
		}
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
			case 'Expr_BinaryOp_Equal':
				if ($leftValue == $rightValue) {
					return ConditionCoupling::TYPE_IDENTICAL;
				} else {
					return ConditionCoupling::TYPE_WEAK_DISJOINT;
				}
				break;

			case 'Expr_BinaryOp_NotEqual':
				throw new \RuntimeException('Not implemented');
				break;

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
				if ($leftValue < $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				} elseif ($leftValue > $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				} elseif ($leftValue == $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				}

				break;

			case 'Expr_BinaryOp_Greater':
				// the right expression is a "greater or equal"
				if ($leftValue < $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				} elseif ($leftValue > $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				} elseif ($leftValue == $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				}

				break;

			case 'Expr_BinaryOp_SmallerOrEqual':
				// the right expression is a "smaller"
				if ($leftValue < $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				} elseif ($leftValue > $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				} elseif ($leftValue == $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				}

				break;

			case 'Expr_BinaryOp_Smaller':
				// the right expression is a "smaller or equal"
				if ($leftValue < $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				} elseif ($leftValue > $rightValue) {
					return ConditionCoupling::TYPE_SUPERSET;
				} elseif ($leftValue == $rightValue) {
					return ConditionCoupling::TYPE_SUBSET;
				}

				break;
		}
		return NULL; // this should never happen
	}

	protected function determineCouplingOfContraryTypeConditions(Expr\BinaryOp $leftExpression,
	                                                             Expr\BinaryOp $rightExpression) {
		$leftValue = $this->extractConstantValue($leftExpression->right);
		$rightValue = $this->extractConstantValue($rightExpression->right);

		switch ($leftExpression->getType()) {
			case 'Expr_BinaryOp_Smaller':
			case 'Expr_BinaryOp_SmallerOrEqual':
				if ($leftValue < $rightValue) {
					return ConditionCoupling::TYPE_WEAK_DISJOINT;
				} elseif ($leftValue > $rightValue) {
					return ConditionCoupling::TYPE_WEAK_OVERLAPPING;
				} else {
					if ($leftExpression instanceof Expr\BinaryOp\Smaller) {
						if ($rightExpression instanceof Expr\BinaryOp\GreaterOrEqual) {
							return ConditionCoupling::TYPE_STRONG;
						} else {
							return ConditionCoupling::TYPE_WEAK_DISJOINT;
						}
					} else {
						if ($rightExpression instanceof Expr\BinaryOp\GreaterOrEqual) {
							return ConditionCoupling::TYPE_WEAK_OVERLAPPING;
						} else {
							return ConditionCoupling::TYPE_STRONG;
						}
					}
				}

				break;
			case 'Expr_BinaryOp_Greater':
			case 'Expr_BinaryOp_GreaterOrEqual':
				if ($leftValue < $rightValue) {
					return ConditionCoupling::TYPE_WEAK_OVERLAPPING;
				} elseif ($leftValue > $rightValue) {
					return ConditionCoupling::TYPE_WEAK_DISJOINT;
				} else {
					if ($leftExpression instanceof Expr\BinaryOp\Greater) {
						if ($rightExpression instanceof Expr\BinaryOp\SmallerOrEqual) {
							return ConditionCoupling::TYPE_STRONG;
						} else {
							return ConditionCoupling::TYPE_WEAK_DISJOINT;
						}
					} else {
						if ($rightExpression instanceof Expr\BinaryOp\SmallerOrEqual) {
							return ConditionCoupling::TYPE_WEAK_OVERLAPPING;
						} else {
							return ConditionCoupling::TYPE_STRONG;
						}
					}
				}

				break;
		}
	}

	protected function determineCouplingOfExpressionsConditionsWithEqualOnOneSide(Expr\BinaryOp $leftExpression,
	                                                                              Expr\BinaryOp $rightExpression) {
		$leftValue = $this->extractConstantValue($leftExpression->right);
		$rightValue = $this->extractConstantValue($rightExpression->right);

		if ($leftExpression instanceof Expr\BinaryOp\Equal) {
			switch ($rightExpression->getType()) {
				case 'Expr_BinaryOp_Smaller':
				case 'Expr_BinaryOp_SmallerOrEqual':
					if ($leftValue < $rightValue) {
						return ConditionCoupling::TYPE_SUBSET;
					} elseif ($leftValue > $rightValue) {
						return ConditionCoupling::TYPE_WEAK_DISJOINT;
					} else {
						if ($rightExpression instanceof Expr\BinaryOp\Smaller) {
							return ConditionCoupling::TYPE_WEAK_DISJOINT;
						} else {
							return ConditionCoupling::TYPE_SUBSET;
						}
					}

					break;
				case 'Expr_BinaryOp_Greater':
				case 'Expr_BinaryOp_GreaterOrEqual':
					if ($leftValue < $rightValue) {
						return ConditionCoupling::TYPE_WEAK_DISJOINT;
					} elseif ($leftValue > $rightValue) {
						return ConditionCoupling::TYPE_SUBSET;
					} else {
						if ($rightExpression instanceof Expr\BinaryOp\Greater) {
							return ConditionCoupling::TYPE_WEAK_DISJOINT;
						} else {
							return ConditionCoupling::TYPE_SUBSET;
						}
					}

					break;
			}
		} elseif ($rightExpression instanceof Expr\BinaryOp\Equal) {
			switch ($leftExpression->getType()) {
				case 'Expr_BinaryOp_Smaller':
				case 'Expr_BinaryOp_SmallerOrEqual':
					if ($leftValue < $rightValue) {
						return ConditionCoupling::TYPE_WEAK_DISJOINT;
					} elseif ($leftValue > $rightValue) {
						return ConditionCoupling::TYPE_SUPERSET;
					} else {
						if ($leftExpression instanceof Expr\BinaryOp\Smaller) {
							return ConditionCoupling::TYPE_WEAK_DISJOINT;
						} else {
							return ConditionCoupling::TYPE_SUPERSET;
						}
					}

					break;
				case 'Expr_BinaryOp_Greater':
				case 'Expr_BinaryOp_GreaterOrEqual':
					if ($leftValue < $rightValue) {
						return ConditionCoupling::TYPE_SUPERSET;
					} elseif ($leftValue > $rightValue) {
						return ConditionCoupling::TYPE_WEAK_DISJOINT;
					} else {
						if ($leftExpression instanceof Expr\BinaryOp\Greater) {
							return ConditionCoupling::TYPE_WEAK_DISJOINT;
						} else {
							return ConditionCoupling::TYPE_SUPERSET;
						}
					}

					break;
			}
		}
		return NULL;
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

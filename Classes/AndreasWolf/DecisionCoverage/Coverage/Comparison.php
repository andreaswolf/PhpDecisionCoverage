<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use PhpParser\Node\Expr;
use Symfony\Component\DependencyInjection\Variable;


/**
 * Currently only a container for comparison-related constants.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Comparison {

	const EQUAL = '==';
	const NOT_EQUAL = '!=';
	const IDENTICAL = '!==';
	const NOT_IDENTICAL = '===';
	const GREATER = '>';
	const GREATER_OR_EQUAL = '>=';
	const SMALLER = '<';
	const SMALLER_OR_EQUAL = '<=';

	/**
	 * The inverse of each comparison. This is not usable to flip the sides of a comparison expression, because
	 * it will not keep the boundaries for ordered values (e.g. "smaller" becomes "greater or equal") and will
	 * change to inequality for equality comparisons (and vice versa), though these should not change when flipping
	 * sides.
	 *
	 * @var array
	 */
	public static $inverseComparison = array(
		self::EQUAL => self::NOT_EQUAL,
		self::NOT_EQUAL  => self::EQUAL,
		self::IDENTICAL => self::NOT_IDENTICAL,
		self::NOT_IDENTICAL => self::IDENTICAL,
		self::GREATER => self::SMALLER_OR_EQUAL,
		self::GREATER_OR_EQUAL => self::SMALLER,
		self::SMALLER => self::GREATER_OR_EQUAL,
		self::SMALLER_OR_EQUAL => self::GREATER,
	);

	/**
	 * Mapping of the (mathematical) operators to PhpParser’s node type strings.
	 *
	 * @var array
	 */
	protected static $operatorToTypeMapping = array(
		self::EQUAL => 'Expr_BinaryOp_Equal',
		self::NOT_EQUAL => 'Expr_BinaryOp_NotEqual',
		self::IDENTICAL => 'Expr_BinaryOp_Identical',
		self::NOT_IDENTICAL => 'Expr_BinaryOp_NotIdentical',
		self::GREATER => 'Expr_BinaryOp_Greater',
		self::GREATER_OR_EQUAL => 'Expr_BinaryOp_GreaterOrEqual',
		self::SMALLER => 'Expr_BinaryOp_Smaller',
		self::SMALLER_OR_EQUAL => 'Expr_BinaryOp_SmallerOrEqual',
	);

	/**
	 * The operators that can be written in Yoda notation and change when changing back to normal notation.
	 *
	 * The so-called Yoda notation inverts the order of variable and comparison value to avoid accidental assignments
	 * in a comparison ($foo = "bar" instead of $foo == "bar")
	 * An example for Yoda notation would be "bar" == $foo. When writing "bar" = $foo, a compiler error will be
	 * triggered because a static string cannot change its value.
	 *
	 * @var array
	 */
	public static $invertedYodaNotationOperators = array(
		self::GREATER => self::SMALLER,
		self::GREATER_OR_EQUAL => self::SMALLER_OR_EQUAL,
		self::SMALLER => self::GREATER,
		self::SMALLER_OR_EQUAL => self::GREATER_OR_EQUAL,
	);

	/**
	 * Returns TRUE if the given comparison checks for inequality of the two sides.
	 *
	 * @param Expr\BinaryOp $comparison
	 * @return bool
	 */
	public function testsInequality(Expr\BinaryOp $comparison) {
		return $comparison instanceof Expr\BinaryOp\NotEqual || $comparison instanceof Expr\BinaryOp\NotIdentical
			|| $comparison instanceof Expr\BinaryOp\Greater || $comparison instanceof Expr\BinaryOp\Smaller;
	}

	/**
	 * Brings a comparison expression into a canonical form, i.e. the variable on the left and the compared value on
	 * the right side. If both parts are variables, the expression is left unchanged.
	 *
	 * Note that this does not copy any attributes etc., so the returned expression can probably not be used as a
	 * drop-in replacement for the passed expression.
	 *
	 * @param Expr\BinaryOp $expression
	 * @return Expr\BinaryOp
	 */
	public static function canonicalize(Expr\BinaryOp $expression) {
		// if there is just one variable and its on the left, we don’t have to do anything
		if ($expression->right instanceof Expr\Variable && !($expression->left instanceof Variable)) {
			$operatorType = array_search($expression->getType(), self::$operatorToTypeMapping);

			if (in_array($operatorType, self::$invertedYodaNotationOperators)) {
				// the operator is one of [<, >, <=, >=], so we have to invert it and return a new expression object
				$invertedOperator = self::$invertedYodaNotationOperators[$operatorType];
				$invertedOperatorType = self::$operatorToTypeMapping[$invertedOperator];

				$newExpressionClass = 'PhpParser\\Node\\' . str_replace('_', '\\', $invertedOperatorType);
				$newExpression = new $newExpressionClass($expression->right, $expression->left);

				return $newExpression;
			} else {
				$newRight = $expression->left;
				$expression->left = $expression->right;
				$expression->right = $newRight;
			}
		}

		return $expression;
	}

}
 
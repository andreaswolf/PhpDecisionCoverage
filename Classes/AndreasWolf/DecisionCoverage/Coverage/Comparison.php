<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use PhpParser\Node\Expr;


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
	 * The operators that can be written in Yoda notation and change when changing back to normal notation.
	 *
	 * The so-called Yoda notation inverts the order of variable and comparison value to avoid accidental assignments
	 * in a comparison ($foo = "bar" instead of $foo == "bar")
	 * An example for Yoda notation would be "bar" == $foo. When writing "bar" = $foo, a compiler error will be
	 * triggered because a static string cannot change its value.
	 *
	 * @var array
	 */
	public static $invertedYodaNotationOpertors = array(
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

}
 
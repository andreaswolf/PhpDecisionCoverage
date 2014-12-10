<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Coupling;

use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\LNumber;


class ConditionCoupling {

	const TYPE_UNCOUPLED = 0;
	const TYPE_STRONG = 1; // probably this should be renamed to "opposite"
	const TYPE_WEAK_DISJUNCT = 2;
	const TYPE_WEAK_OVERLAPPING = 3;
	const TYPE_SUPERSET = 4;
	const TYPE_SUBSET = 5;
	const TYPE_IDENTICAL = 6;

	/**
	 * @var int
	 */
	protected $type = self::TYPE_UNCOUPLED;

	/**
	 * @param Expr\BinaryOp $leftExpression
	 * @param Expr\BinaryOp $rightExpression
	 */
	public function __construct(Expr\BinaryOp $leftExpression, Expr\BinaryOp $rightExpression) {
		// TODO canonicalize expressions, check coupling type
	}

	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}

}

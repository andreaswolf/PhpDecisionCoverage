<?php
namespace AndreasWolf\DecisionCoverage\BooleanLogic;

use AndreasWolf\DecisionCoverage\Source\ComparisonExtractor;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;


/**
 * Abstraction for a boolean condition.
 *
 * Such a condition can consist of one or more comparisons or other expressions that can be evaluated as a boolean
 * (e.g. "$foo" will test if the contents of $foo is anything that can be evaluated as a boolean TRUE).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BooleanCondition {

	/**
	 * @var Expr
	 */
	protected $expression;

	/**
	 * @param Stmt $parentStatement An if or elseif statement
	 * @throws \InvalidArgumentException If the statement is not of the right type
	 */
	public function __construct(Stmt $parentStatement) {
		if (!($parentStatement instanceof Stmt\If_ || $parentStatement instanceof Stmt\ElseIf_)) {
			throw new \InvalidArgumentException('Given statement is no "if" or "elseif"');
		}
		$this->expression = $parentStatement->cond;
	}

	/**
	 * Returns all parts of this condition.
	 *
	 * Note that this will also return e.g. the simple expression "$foo" if present in the statement, though this is
	 * not strictly a comparison, just used as such here.
	 *
	 * @return array
	 */
	public function getAllConditionParts() {
		$comparisonExtractor = new ComparisonExtractor();

		return $comparisonExtractor->extractComparisons($this->expression);
	}

	/**
	 * @return Expr
	 */
	public function getExpression() {
		return $this->expression;
	}

}

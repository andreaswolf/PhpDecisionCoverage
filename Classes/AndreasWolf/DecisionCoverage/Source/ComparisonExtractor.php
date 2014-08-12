<?php
namespace AndreasWolf\DecisionCoverage\Source;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\If_;


/**
 * Extractor for comparisons in various places.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ComparisonExtractor {

	/**
	 * Extracts all comparisons from the condition of an if statement node. As the operators are ignored,
	 * the precedence of the single operations is also lost along the way.
	 *
	 * @param If_ $ifNode
	 * @return Expr[]
	 */
	public function extractFromIf(If_ $ifNode) {
		$condition = $ifNode->cond;

		return $this->extractCondition($condition);
	}

	/**
	 * Extracts the conditions from an expression. This can also traverse deeply nested trees of ANDs and ORs and
	 * removes inversions (boolean NOT) where possible.
	 *
	 * @param Expr $expression
	 * @return array|void
	 */
	protected function extractCondition(Expr $expression) {
		if ($this->isComparison($expression)) {
			return array($expression);
		} else if ($this->isConjunction($expression)) {
			/** @var Expr\BinaryOp $expression */
			$comparisons = array();
			foreach (array($expression->left, $expression->right) as $expression) {
				$comparisons = array_merge($comparisons, $this->extractCondition($expression));
			}
			return $comparisons;
		} else if ($expression instanceof Expr\BooleanNot) {
			return $this->extractCondition($this->removeBooleanNot($expression));
		}
	}

	/**
	 * Checks if the given expression is a comparison.
	 *
	 * @param Expr $expression
	 * @return bool
	 */
	protected function isComparison(Expr $expression) {
		return $expression instanceof Expr\BinaryOp\Equal || $expression instanceof Expr\BinaryOp\NotEqual
			|| $expression instanceof Expr\BinaryOp\Identical || $expression instanceof Expr\BinaryOp\NotIdentical
			|| $expression instanceof Expr\BinaryOp\Greater || $expression instanceof Expr\BinaryOp\GreaterOrEqual
			|| $expression instanceof Expr\BinaryOp\Smaller || $expression instanceof Expr\BinaryOp\SmallerOrEqual;
	}

	/**
	 * Checks if the given expression is a conjunction of two other expressions (boolean "and" or boolean "or")
	 *
	 * @param Expr $expression
	 * @return bool
	 */
	protected function isConjunction(Expr $expression) {
		return $expression instanceof Expr\BinaryOp\BooleanAnd || $expression instanceof Expr\BinaryOp\BooleanOr
			|| $expression instanceof Expr\BinaryOp\LogicalAnd || $expression instanceof Expr\BinaryOp\LogicalOr
			|| $expression instanceof Expr\BinaryOp\LogicalXor;
	}

	/**
	 * Removes a boolean "not" by inverting the condition(s) inside it.
	 *
	 * @param Expr\BooleanNot $booleanNot
	 * @return Expr
	 */
	protected function removeBooleanNot(Expr\BooleanNot $booleanNot) {
		if ($this->isComparison($booleanNot->expr)) {
			return $this->invertComparison($booleanNot->expr);
		}
	}

	/**
	 * Inverts the given comparison, keeping left and right side in their place. This can be used to e.g.
	 * remove a boolean not in front of the expression
	 *
	 * @param Expr\BinaryOp $expression
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected function invertComparison(Expr\BinaryOp $expression) {
		$inverseOperation = $this->getInverseComparison($expression);

		if ($inverseOperation === NULL) {
			throw new \RuntimeException('No inverse found for operation ' . get_class($expression));
		}

		return new $inverseOperation($expression->left, $expression->right);
	}

	/**
	 *
	 * @param Expr\BinaryOp $expression
	 * @return string|NULL The class name of the inverted comparison or NULL if the comparison cannot be inverted (should never happen)
	 */
	protected function getInverseComparison(Expr\BinaryOp $expression) {
		$inverseOperation = NULL;
		switch (TRUE) {
			case $expression instanceof Expr\BinaryOp\Equal:
				$inverseOperation = 'PhpParser\\Node\\Expr\\BinaryOp\\NotEqual';
				break;
			case $expression instanceof Expr\BinaryOp\NotEqual:
				$inverseOperation = 'PhpParser\\Node\\Expr\\BinaryOp\\Equal';
				break;
			case $expression instanceof Expr\BinaryOp\Identical:
				$inverseOperation = 'PhpParser\\Node\\Expr\\BinaryOp\\NotIdentical';
				break;
			case $expression instanceof Expr\BinaryOp\NotIdentical:
				$inverseOperation = 'PhpParser\\Node\\Expr\\BinaryOp\\Identical';
				break;
			case $expression instanceof Expr\BinaryOp\Greater:
				$inverseOperation = 'PhpParser\\Node\\Expr\\BinaryOp\\SmallerOrEqual';
				break;
			case $expression instanceof Expr\BinaryOp\GreaterOrEqual:
				$inverseOperation = 'PhpParser\\Node\\Expr\\BinaryOp\\Smaller';
				break;
			case $expression instanceof Expr\BinaryOp\Smaller:
				$inverseOperation = 'PhpParser\\Node\\Expr\\BinaryOp\\GreaterOrEqual';
				break;
			case $expression instanceof Expr\BinaryOp\SmallerOrEqual:
				$inverseOperation = 'PhpParser\\Node\\Expr\\BinaryOp\\Greater';
				break;
		}
		return $inverseOperation;
	}

}
 
<?php
namespace AndreasWolf\DecisionCoverage\Coverage;
use PhpParser\Node\Expr;
use PhpParser\Node;


/**
 * A variable that is used in the code executed in a method, thus influences the
 * method results and has to be tested.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TestVariable {

	/**
	 * The values the variable is tested for. This includes both the value and the operator, i.e.
	 * ==, ===, >=, <=, >, <, !=
	 * Not all operators are available for all value types.
	 *
	 * @var array
	 */
	protected $testedValues;

	public function __construct(Expr $comparisonNode) {
		$this->addTestedValueFromNode($comparisonNode);
	}

	protected function addTestedValueFromNode(Expr\BinaryOp $comparisonNode) {
		$comparisonType = $this->getComparisonType($comparisonNode);

		$foundComparisonValue = $yodaNotation = FALSE;
		if ($comparisonNode->left instanceof Node\Scalar) {
			$comparisonValue = $comparisonNode->left->value;
			$foundComparisonValue = TRUE;
			$yodaNotation = TRUE;
		}
		if ($comparisonNode->right instanceof Node\Scalar) {
			$comparisonValue = $comparisonNode->right->value;
			$foundComparisonValue = TRUE;
		}
		if (!$foundComparisonValue) {
			throw new \RuntimeException('Could not find comparison value for expression');
		}
		if ($yodaNotation && isset(Comparison::$invertedYodaNotationOperators[$comparisonType])) {
			$comparisonType = Comparison::$invertedYodaNotationOperators[$comparisonType];
		}

		$this->testedValues[] = array($comparisonType, $comparisonValue);
	}

	/**
	 * Adds the compared value and comparison operator from the given comparison expression.
	 *
	 * Does not check if the variable name matches, you have to do this yourself!
	 *
	 * @param Expr\BinaryOp $comparisonNode
	 */
	public function addValueTest(Expr\BinaryOp $comparisonNode) {
		$this->addTestedValueFromNode($comparisonNode);
	}

	protected function getComparisonType(Expr $comparisonNode) {
		switch (TRUE) {
			case $comparisonNode instanceof Expr\BinaryOp\Equal:
				return Comparison::EQUAL;
			case $comparisonNode instanceof Expr\BinaryOp\NotEqual:
				return Comparison::NOT_EQUAL;
			case $comparisonNode instanceof Expr\BinaryOp\Identical:
				return Comparison::IDENTICAL;
			case $comparisonNode instanceof Expr\BinaryOp\Greater:
				return Comparison::GREATER;
			case $comparisonNode instanceof Expr\BinaryOp\GreaterOrEqual:
				return Comparison::GREATER_OR_EQUAL;
			case $comparisonNode instanceof Expr\BinaryOp\Smaller:
				return Comparison::SMALLER;
			case $comparisonNode instanceof Expr\BinaryOp\SmallerOrEqual:
				return Comparison::SMALLER_OR_EQUAL;
		}

		throw new \InvalidArgumentException(get_class($comparisonNode) . ' is no valid comparison');
	}

	/**
	 * @return array
	 */
	public function getTestedValues() {
		return $this->testedValues;
	}

}

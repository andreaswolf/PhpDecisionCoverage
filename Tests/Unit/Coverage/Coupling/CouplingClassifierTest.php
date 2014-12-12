<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Coupling;

use AndreasWolf\DecisionCoverage\Coverage\Coupling\ConditionCoupling;
use AndreasWolf\DecisionCoverage\Coverage\Coupling\CouplingClassifier;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class CouplingClassifierTest extends UnitTestCase {

	const OPERATOR_SMALLER = '<';
	const OPERATOR_GREATER = '>';
	const OPERATOR_SMALLER_OR_EQUAL = '<=';
	const OPERATOR_GREATER_OR_EQUAL = '>=';
	const OPERATOR_EQUAL = '==';
	const OPERATOR_NOT_EQUAL = '!=';

	/**
	 * @test
	 */
	public function unrelatedExpressionsAreConsideredUncoupled() {
		$subject = new CouplingClassifier();

		$coupling = $subject->determineConditionCoupling(
			$this->mockGreater($this->mockVariable('a'), $this->mockInteger(5)),
			$this->mockGreater($this->mockVariable('b'), $this->mockInteger(0))
		);

		$this->assertEquals(ConditionCoupling::TYPE_UNCOUPLED, $coupling, 'Conditions are not recognized as uncoupled.');
	}

	/**
	 * Provider for all relation type combinations and the expected value.
	 *
	 * @return array
	 */
	public function relationTypesProvider() {
		// first level = left expression type, second level: right expression type
		return array(
			self::OPERATOR_SMALLER => array(
				self::OPERATOR_SMALLER => array(
					ConditionCoupling::TYPE_SUBSET,    // left value < right value
					ConditionCoupling::TYPE_SUPERSET,  // left value > right value
					ConditionCoupling::TYPE_IDENTICAL, // left value == right value
				),
				self::OPERATOR_SMALLER_OR_EQUAL => array(
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_SUBSET,
				),
				self::OPERATOR_GREATER => array(
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
				),
				self::OPERATOR_GREATER_OR_EQUAL => array(
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
					ConditionCoupling::TYPE_STRONG,
				),
				self::OPERATOR_EQUAL => array(
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
				),
				self::OPERATOR_NOT_EQUAL => array(
					// TODO implement
				)
			),
			self::OPERATOR_SMALLER_OR_EQUAL => array(
				self::OPERATOR_SMALLER => array(
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_SUPERSET,
				),
				self::OPERATOR_SMALLER_OR_EQUAL => array(
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_IDENTICAL,
				),
				self::OPERATOR_GREATER => array(
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
					ConditionCoupling::TYPE_STRONG,
				),
				self::OPERATOR_GREATER_OR_EQUAL => array(
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
				),
				self::OPERATOR_EQUAL => array(
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_SUPERSET,
				),
				self::OPERATOR_NOT_EQUAL => array(
				)
			),
			self::OPERATOR_GREATER => array(
				self::OPERATOR_SMALLER => array(
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
				),
				self::OPERATOR_SMALLER_OR_EQUAL => array(
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_STRONG,
				),
				self::OPERATOR_GREATER => array(
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_IDENTICAL,
				),
				self::OPERATOR_GREATER_OR_EQUAL => array(
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_SUBSET,
				),
				self::OPERATOR_EQUAL => array(
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
				),
				self::OPERATOR_NOT_EQUAL => array(
				)
			),
			self::OPERATOR_GREATER_OR_EQUAL => array(
				self::OPERATOR_SMALLER => array(
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_STRONG,
				),
				self::OPERATOR_SMALLER_OR_EQUAL => array(
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_WEAK_OVERLAPPING,
				),
				self::OPERATOR_GREATER => array(
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_SUPERSET,
				),
				self::OPERATOR_GREATER_OR_EQUAL => array(
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_IDENTICAL,
				),
				self::OPERATOR_EQUAL => array(
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_SUPERSET,
				),
				self::OPERATOR_NOT_EQUAL => array(
				),
			),
			self::OPERATOR_EQUAL => array(
				self::OPERATOR_SMALLER => array(
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
				),
				self::OPERATOR_SMALLER_OR_EQUAL => array(
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_SUBSET,
				),
				self::OPERATOR_GREATER => array(
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
				),
				self::OPERATOR_GREATER_OR_EQUAL => array(
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_SUBSET,
					ConditionCoupling::TYPE_SUBSET,
				),
				self::OPERATOR_EQUAL => array(
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_WEAK_DISJOINT,
					ConditionCoupling::TYPE_IDENTICAL,
				),
				self::OPERATOR_NOT_EQUAL => array(
				),
			),
			self::OPERATOR_NOT_EQUAL => array(
				self::OPERATOR_SMALLER => array(
				),
				self::OPERATOR_SMALLER_OR_EQUAL => array(
				),
				self::OPERATOR_GREATER => array(
				),
				self::OPERATOR_GREATER_OR_EQUAL => array(
				),
				self::OPERATOR_EQUAL => array(
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_SUPERSET,
					ConditionCoupling::TYPE_STRONG,
				),
				self::OPERATOR_NOT_EQUAL => array(
					-1, // TODO implement
					-1, // TODO implement
					ConditionCoupling::TYPE_IDENTICAL,
				),
			),
		);
	}

	/**
	 * Data provider for tests with similar types
	 *
	 * @return array
	 */
	public function sameExpressionTypeDataProvider() {
		$tests = array();

		foreach ($this->relationTypesProvider() as $type => $rightTypes) {
			$expectedValues = $rightTypes[$type];

			$this->addTestsForRelationType($tests, $type, $type, $expectedValues);
		}

		return $tests;
	}

	/**
	 * @test
	 * @dataProvider sameExpressionTypeDataProvider
	 */
	public function relationsOfSameTypeAreCorrectlyHandled($leftOperator, $leftValue, $rightOperator, $rightValue, $expectedValue) {
		$this->runTestForExpressionType($leftOperator, $rightOperator, $leftValue, $rightValue, $expectedValue);
	}

	/**
	 * Data provider for tests with similar relation types.
	 */
	public function similarExpressionTypesDataProvider() {
		$similarExpressionMappings = [
			self::OPERATOR_GREATER => [self::OPERATOR_GREATER_OR_EQUAL],
			self::OPERATOR_GREATER_OR_EQUAL => [self::OPERATOR_GREATER],
			self::OPERATOR_SMALLER => [self::OPERATOR_SMALLER_OR_EQUAL],
			self::OPERATOR_SMALLER_OR_EQUAL => [self::OPERATOR_SMALLER],
		];

		$relationTypes = $this->relationTypesProvider();

		$tests = array();
		foreach ($similarExpressionMappings as $leftType => $rightTypes) {
			foreach ($rightTypes as $rightType) {
				$this->addTestsForRelationType($tests, $leftType, $rightType, $relationTypes[$leftType][$rightType]);
			}
		}

		return $tests;
	}

	/**
	 * @test
	 * @dataProvider similarExpressionTypesDataProvider
	 */
	public function relationsOfSimilarTypesAreCorrectlyHandled($leftOperator, $leftValue, $rightOperator, $rightValue, $expectedValue) {
		$this->runTestForExpressionType($leftOperator, $rightOperator, $leftValue, $rightValue, $expectedValue);
	}

	public function contraryExpressionTypesProvider() {
		$contraryExpressionTypes = [
			self::OPERATOR_GREATER => [self::OPERATOR_SMALLER, self::OPERATOR_SMALLER_OR_EQUAL],
			self::OPERATOR_GREATER_OR_EQUAL => [self::OPERATOR_SMALLER, self::OPERATOR_SMALLER_OR_EQUAL],
			self::OPERATOR_SMALLER => [self::OPERATOR_GREATER, self::OPERATOR_GREATER_OR_EQUAL],
			self::OPERATOR_SMALLER_OR_EQUAL => [self::OPERATOR_GREATER, SELF::OPERATOR_GREATER_OR_EQUAL],
		];

		$relationTypes = $this->relationTypesProvider();

		$tests = array();
		foreach ($contraryExpressionTypes as $leftType => $rightTypes) {
			foreach ($rightTypes as $rightType) {
				$this->addTestsForRelationType($tests, $leftType, $rightType, $relationTypes[$leftType][$rightType]);
			}
		}

		return $tests;
	}

	/**
	 * @test
	 * @dataProvider contraryExpressionTypesProvider
	 */
	public function relationsOfContraryTypesAreCorrectlyHandled($leftOperator, $leftValue, $rightOperator, $rightValue, $expectedValue) {
		$this->runTestForExpressionType($leftOperator, $rightOperator, $leftValue, $rightValue, $expectedValue);
	}

	/**
	 * @param string $leftType
	 * @param string $rightType
	 * @param int $leftExpressionValue
	 * @param int $rightExpressionValue
	 * @param int $expectedCoupling
	 */
	protected function runTestForExpressionType($leftType, $rightType, $leftExpressionValue, $rightExpressionValue, $expectedCoupling) {
		$leftExpression = $this->expression($leftType, $this->mockInteger($leftExpressionValue));
		$rightExpression = $this->expression($rightType, $this->mockInteger($rightExpressionValue));

		$subject = new CouplingClassifier();

		$coupling = $subject->determineConditionCoupling($leftExpression, $rightExpression);

		$this->assertEquals($expectedCoupling, $coupling);
	}


	/**
	 * @param array $tests
	 * @param string $leftType The left relation type
	 * @param string $rightType The right relation type
	 * @param array $expectedValues The expected values (in this order) for the value relations <, > and ==
	 */
	protected function addTestsForRelationType(&$tests, $leftType, $rightType, $expectedValues) {
		$dataSetName = 'x ' . $leftType . ' m, x ' . $rightType . ' n';

		$tests[$dataSetName . ', m < n'] = array($leftType, 0, $rightType, 5, $expectedValues[0]);
		$tests[$dataSetName . ', m > n'] = array($leftType, 10, $rightType, 5, $expectedValues[1]);
		$tests[$dataSetName . ', m = n'] = array($leftType, 5, $rightType, 5, $expectedValues[2]);
	}

	/**
	 * Creates an expression of the given type.
	 *
	 * @param $type
	 * @param $value
	 * @param null $variable
	 * @return Expr\BinaryOp\Greater|Expr\BinaryOp\GreaterOrEqual|Expr\BinaryOp\Smaller|Expr\BinaryOp\SmallerOrEqual
	 */
	protected function expression($type, $value, $variable = NULL) {
		if ($variable == NULL) {
			$variable = $this->mockVariable('a');
		}

		switch ($type) {
			case self::OPERATOR_SMALLER:
				return $this->mockSmaller($variable, $value);

			case self::OPERATOR_SMALLER_OR_EQUAL:
				return $this->mockSmallerOrEqual($variable, $value);

			case self::OPERATOR_GREATER:
				return $this->mockGreater($variable, $value);

			case self::OPERATOR_GREATER_OR_EQUAL:
				return $this->mockGreaterOrEqual($variable, $value);

			case self::OPERATOR_EQUAL:
				return $this->mockEqual($variable, $value);

			case self::OPERATOR_NOT_EQUAL:
				return $this->mockNotEqual($variable, $value);
		}
	}

	protected function mockGreater($left, $right) {
		return new Expr\BinaryOp\Greater($left, $right);
	}

	protected function mockGreaterOrEqual($left, $right) {
		return new Expr\BinaryOp\GreaterOrEqual($left, $right);
	}

	protected function mockSmaller($left, $right) {
		return new Expr\BinaryOp\Smaller($left, $right);
	}

	protected function mockSmallerOrEqual($left, $right) {
		return new Expr\BinaryOp\SmallerOrEqual($left, $right);
	}

	protected function mockEqual($left, $right) {
		return new Expr\BinaryOp\Equal($left, $right);
	}

	protected function mockNotEqual($left, $right) {
		return new Expr\BinaryOp\NotEqual($left, $right);
	}

	protected function mockVariable($variableName) {
		return new Expr\Variable($variableName);
	}

	protected function mockInteger($value) {
		return new Scalar\LNumber($value);
	}

}

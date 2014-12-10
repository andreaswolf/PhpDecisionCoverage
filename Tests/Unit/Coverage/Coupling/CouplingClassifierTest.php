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

	public function identicalExpressionsDataProvider() {
		return array(
			'greater' => array($this->mockGreater($this->mockVariable('a'), $this->mockInteger(5))),
			'greater or equal' => array($this->mockGreaterOrEqual($this->mockVariable('a'), $this->mockInteger(5))),
			'smaller' => array($this->mockSmaller($this->mockVariable('a'), $this->mockInteger(5))),
			'smaller or equal' => array($this->mockSmallerOrEqual($this->mockVariable('a'), $this->mockInteger(5))),
		);
	}

	/**
	 * @test
	 * @dataProvider identicalExpressionsDataProvider
	 */
	public function identicalExpressionsAreClassifiedCorrectly($expression) {
		$subject = new CouplingClassifier();

		$coupling = $subject->determineConditionCoupling(
			$expression,
			$expression
		);

		$this->assertEquals(ConditionCoupling::TYPE_IDENTICAL, $coupling, 'Conditions are not recognized as identical.');
	}

	/**
	 * The variables returned by this provider can be used for both testing super- and subset relations by switching
	 * the two expressions (due to the antisymmetrical nature of these relation types).
	 *
	 * @return array
	 */
	public function sameTypeRelationalExpressionProvider() {
		return array(
			'greater than' => array(
				$this->mockGreater($this->mockVariable('a'), $this->mockInteger(5)),
				$this->mockGreater($this->mockVariable('a'), $this->mockInteger(10)),
			),
			'greater or equal' => array(
				$this->mockGreaterOrEqual($this->mockVariable('a'), $this->mockInteger(5)),
				$this->mockGreaterOrEqual($this->mockVariable('a'), $this->mockInteger(10)),
			),
			'smaller than' => array(
				$this->mockSmaller($this->mockVariable('a'), $this->mockInteger(10)),
				$this->mockSmaller($this->mockVariable('a'), $this->mockInteger(5)),
			),
			'smaller or equal' => array(
				$this->mockSmallerOrEqual($this->mockVariable('a'), $this->mockInteger(10)),
				$this->mockSmallerOrEqual($this->mockVariable('a'), $this->mockInteger(5)),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider sameTypeRelationalExpressionProvider
	 */
	public function relationalSupersetExpressionsWithSameTypeAndVariableAreCorrectlyClassified($leftExpression, $rightExpression) {
		$subject = new CouplingClassifier();

		$coupling = $subject->determineConditionCoupling($leftExpression, $rightExpression);

		$this->assertEquals(ConditionCoupling::TYPE_SUPERSET, $coupling, 'Condition coupling is not recognized as superset');
	}

	/**
	 * The same test as above, just with switched parameters.
	 *
	 * @test
	 * @dataProvider sameTypeRelationalExpressionProvider
	 */
	public function relationalSubsetExpressionsWithSameTypeAndVariableAreCorrectlyClassified($rightExpression, $leftExpression) {
		$subject = new CouplingClassifier();

		$coupling = $subject->determineConditionCoupling($leftExpression, $rightExpression);

		$this->assertEquals(ConditionCoupling::TYPE_SUBSET, $coupling, 'Condition coupling is not recognized as subset');
	}

	/**
	 * The variables returned by this provider can be used for both testing super- and subset relations by switching
	 * the two expressions (due to the antisymmetrical nature of these relation types).
	 *
	 * The two relational operators >/>= and </<= form a super-/subset relation when fed with the same values as the
	 * one that includes "equals" also includes the compared value, while the other excludes it.
	 *
	 * @return array
	 */
	public function similarTypeAndSameValueRelationalExpressionProvider() {
		return array(
			'greater or equal and greater' => array(
				$this->mockGreaterOrEqual($this->mockVariable('a'), $this->mockInteger(5)),
				$this->mockGreater($this->mockVariable('a'), $this->mockInteger(5)),
			),
			'smaller or equal and smaller' => array(
				$this->mockSmallerOrEqual($this->mockVariable('a'), $this->mockInteger(5)),
				$this->mockSmaller($this->mockVariable('a'), $this->mockInteger(5)),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider similarTypeAndSameValueRelationalExpressionProvider
	 */
	public function supersetIsCorrectlyClassifiedForSameVariableValue($leftExpression, $rightExpression) {
		$subject = new CouplingClassifier();

		$coupling = $subject->determineConditionCoupling($leftExpression, $rightExpression);

		$this->assertEquals(ConditionCoupling::TYPE_SUPERSET, $coupling, 'Condition coupling is not recognized as superset');
	}

	/**
	 * The same as the test above, just with switched parameters, thus leading to sub- instead of supersets.
	 *
	 * @test
	 * @dataProvider similarTypeAndSameValueRelationalExpressionProvider
	 */
	public function subsetIsCorrectlyClassifiedForSameVariableValue($rightExpression, $leftExpression) {
		$subject = new CouplingClassifier();

		$coupling = $subject->determineConditionCoupling($leftExpression, $rightExpression);

		$this->assertEquals(ConditionCoupling::TYPE_SUBSET, $coupling, 'Condition coupling is not recognized as subset');
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

	protected function mockVariable($variableName) {
		return new Expr\Variable($variableName);
	}

	protected function mockInteger($value) {
		return new Scalar\LNumber($value);
	}

}

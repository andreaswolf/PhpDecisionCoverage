<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Weighting;

use AndreasWolf\DecisionCoverage\Coverage\Weighting\BooleanAndWeight;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


/**
 * Tests the weight for a boolean AND (&&).
 *
 * The weight is calculated using the cases Fx, TF and TT (x = not evaluated).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BooleanAndWeightTest extends UnitTestCase {
	use DecisionWeightTestHelper;

	public function expressionWeightDataProvider() {
		return array(
			array(
				array(1, 2), // left: TRUE, FALSE
				array(4, 5), // right: TRUE, FALSE
				4, 7), // expected TRUE, expected FALSE
		);
	}

	/**
	 * @param int[] $leftWeights
	 * @param int[] $rightWeights
	 * @param int $expectedTrueValue
	 * @param int $expectedFalseValue
	 * @test
	 * @dataProvider expressionWeightDataProvider
	 */
	public function getTrueValueReturnsCorrectValue($leftWeights, $rightWeights, $expectedTrueValue, $expectedFalseValue) {
		$leftMock = $this->mockExpressionWeight($leftWeights[0], $leftWeights[1]);
		$rightMock = $this->mockExpressionWeight($rightWeights[0], $rightWeights[1]);

		$subject = new BooleanAndWeight($leftMock, $rightMock, $this->getMock('PhpParser\Node\Expr'));

		$this->assertEquals($expectedTrueValue, $subject->getTrueValue());
	}

	/**
	 * @param int[] $leftWeights
	 * @param int[] $rightWeights
	 * @param int $expectedTrueValue
	 * @param int $expectedFalseValue
	 * @test
	 * @dataProvider expressionWeightDataProvider
	 */
	public function getFalseValueReturnsCorrectValue($leftWeights, $rightWeights, $expectedTrueValue, $expectedFalseValue) {
		$leftMock = $this->mockExpressionWeight($leftWeights[0], $leftWeights[1]);
		$rightMock = $this->mockExpressionWeight($rightWeights[0], $rightWeights[1]);

		$subject = new BooleanAndWeight($leftMock, $rightMock, $this->getMock('PhpParser\Node\Expr'));

		$this->assertEquals($expectedFalseValue, $subject->getFalseValue());
	}

	/**
	 * @param int[] $leftWeights
	 * @param int[] $rightWeights
	 * @param int $expectedTrueValue
	 * @param int $expectedFalseValue
	 * @test
	 * @dataProvider expressionWeightDataProvider
	 */
	public function getValueReturnsCorrectValue($leftWeights, $rightWeights, $expectedTrueValue, $expectedFalseValue) {
		$leftMock = $this->mockExpressionWeight($leftWeights[0], $leftWeights[1]);
		$rightMock = $this->mockExpressionWeight($rightWeights[0], $rightWeights[1]);

		$subject = new BooleanAndWeight($leftMock, $rightMock, $this->getMock('PhpParser\Node\Expr'));

		$this->assertEquals($expectedTrueValue + $expectedFalseValue, $subject->getValue());
	}

}

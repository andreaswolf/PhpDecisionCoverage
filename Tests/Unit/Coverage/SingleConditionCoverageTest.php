<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class SingleConditionCoverageTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function coverageIsZeroForUncoveredCondition() {
		$subject = new SingleConditionCoverage($this->getMock('PhpParser\Node\Expr'));

		// also make sure we get back a float
		$this->assertSame(0.0, $subject->getCoverage());
	}

	/**
	 * @test
	 */
	public function conditionIsHalfCoveredForASingleTrueValue() {
		$subject = new SingleConditionCoverage($this->getMock('PhpParser\Node\Expr'));

		$subject->recordCoveredValue($this->getBooleanExpressionValue(TRUE));

		// also make sure we get back a float
		$this->assertSame(0.5, $subject->getCoverage());
	}

	/**
	 * @test
	 */
	public function conditionIsHalfCoveredForASingleFalseValue() {
		$subject = new SingleConditionCoverage($this->getMock('PhpParser\Node\Expr'));

		$subject->recordCoveredValue($this->getBooleanExpressionValue(FALSE));

		// also make sure we get back a float
		$this->assertSame(0.5, $subject->getCoverage());
	}

	/**
	 * @test
	 */
	public function conditionIsFullyCoveredIfTrueAndFalseHaveBeenCoveredOnceEach() {
		$subject = new SingleConditionCoverage($this->getMock('PhpParser\Node\Expr'));

		$subject->recordCoveredValue($this->getBooleanExpressionValue(TRUE));
		$subject->recordCoveredValue($this->getBooleanExpressionValue(FALSE));

		// also make sure we get back a float
		$this->assertSame(1.0, $subject->getCoverage());
	}

	/**
	 * @test
	 */
	public function coverageDoesNotChangeIfOneValueIsCoveredMultipleTimes() {
		$subject = new SingleConditionCoverage($this->getMock('PhpParser\Node\Expr'));

		$subject->recordCoveredValue($this->getBooleanExpressionValue(FALSE));
		$subject->recordCoveredValue($this->getBooleanExpressionValue(FALSE));
		$subject->recordCoveredValue($this->getBooleanExpressionValue(TRUE));
		$subject->recordCoveredValue($this->getBooleanExpressionValue(TRUE));

		// also make sure we get back a float
		$this->assertSame(1.0, $subject->getCoverage());
	}

	/**
	 * @return ExpressionValue
	 */
	protected function getBooleanExpressionValue($value) {
		return new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, $value);
	}

}

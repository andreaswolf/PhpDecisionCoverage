<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Weighting;

use AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightBuilder;
use AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightFactory;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class ExpressionWeightBuilderTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function weightForConditionIsCorrectlyBuilt() {
		$mockedWeightFactory = $this->mockWeightFactory();
		$mockedWeightFactory->expects($this->once())->method('createForCondition');

		$subject = new ExpressionWeightBuilder($mockedWeightFactory);

		$subject->buildForExpression($this->getMock('PhpParser\Node\Expr'));
	}

	/**
	 * @test
	 */
	public function builtConditionWeightIsAttachedToExpression() {
		$mockedWeight = $this->getMock('AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeight');
		$mockedWeightFactory = $this->mockWeightFactory();
		$mockedWeightFactory->expects($this->once())->method('createForCondition')
			->will($this->returnValue($mockedWeight));

		$subject = new ExpressionWeightBuilder($mockedWeightFactory);

		$expression = $this->mockConditionExpression();
		$expression->expects($this->once())->method('setAttribute')
			->with($this->equalTo('coverage__weight'), $this->equalTo($mockedWeight));
		$subject->buildForExpression($expression);
	}

	/**
	 * @return ExpressionWeightFactory
	 */
	protected function mockWeightFactory() {
		$mockedWeightFactory = $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightFactory')
			->getMock();

		return $mockedWeightFactory;
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockConditionExpression() {
		return $this->getMock('PhpParser\Node\Expr');
	}

}

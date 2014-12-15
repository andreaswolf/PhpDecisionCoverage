<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Weighting;

use AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightBuilder;
use AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightFactory;
use AndreasWolf\DecisionCoverage\Tests\Helpers\PhpParser\ExpressionMockBuilder;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;


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
	 * @test
	 */
	public function weightForDecisionIsBuiltWithCorrectParameters() {
		$mockedWeightA = $this->mockExpressionWeight('Left');
		$mockedWeightB = $this->mockExpressionWeight('Right');
		$mockedDecisionWeight = $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Weighting\DecisionWeight')
			->disableOriginalConstructor()->getMock();

		$decision = new BooleanAnd(
			$this->getExpressionMockBuilder('PhpParser\Node\Expr')->addAttribute('coverage__weight', $mockedWeightA)->getMock(),
			$this->getExpressionMockBuilder('PhpParser\Node\Expr')->addAttribute('coverage__weight', $mockedWeightB)->getMock()
		);

		$mockedWeightFactory = $this->mockWeightFactory();
		$mockedWeightFactory->expects($this->exactly(2))->method('createForCondition')
			// the condition weights are built right to left (as the list is traversed from the back)
			->willReturnOnConsecutiveCalls($mockedWeightB, $mockedWeightA);
		$mockedWeightFactory->expects($this->once())->method('createForDecision')
			->with($this->equalTo($decision), $this->equalTo($mockedWeightA), $this->equalTo($mockedWeightB))
			->willReturn($mockedDecisionWeight);

		$subject = new ExpressionWeightBuilder($mockedWeightFactory);

		$subject->buildForExpression($decision);

		$this->assertEquals($mockedDecisionWeight, $decision->getAttribute('coverage__weight'));
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockWeightFactory() {
		$mockedWeightFactory = $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightFactory')
			->getMock();

		return $mockedWeightFactory;
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockExpressionWeight($classNameSuffix = '') {
		$classNameSuffix = $classNameSuffix ?: uniqid();
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeight')
			->setMockClassName('ExpressionWeight_' . $classNameSuffix)->getMock();
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockConditionExpression() {
		$mock = $this->getMock('PhpParser\Node\Expr');
		$mock->expects($this->any())->method('getType')->willReturn('Expr');

		return $mock;
	}

	protected function getExpressionMockBuilder($type) {
		return new ExpressionMockBuilder($type);
	}

}

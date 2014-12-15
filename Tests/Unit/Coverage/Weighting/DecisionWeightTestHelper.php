<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Weighting;


trait DecisionWeightTestHelper {

	/**
	 * @param $trueWeight
	 * @param $falseWeight
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockExpressionWeight($trueWeight, $falseWeight) {
		$leftMock = $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeight')->getMock();
		$leftMock->expects($this->any())->method('getTrueValue')->willReturn($trueWeight);
		$leftMock->expects($this->any())->method('getFalseValue')->willReturn($falseWeight);

		return $leftMock;
	}

}

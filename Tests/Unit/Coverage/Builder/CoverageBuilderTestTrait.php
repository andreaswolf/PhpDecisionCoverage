<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Builder;


use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Tests\Helpers\PhpUnit\InvocationMatcher as Invocation;


trait CoverageBuilderTestTrait {

	public function getMockBuilder($className) {
		// $this points to the user of this trait, which should be a PHPUnit_Framework_TestCase descendant
		return new \PHPUnit_Framework_MockObject_MockBuilder($this, $className);
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockCoverageFactory() {
		$mockedCoverageFactory = $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageFactory')
			->getMock();
		$mockedCoverageFactory->expects(Invocation::any())->method('createCoverageForNode')->willReturn($this->mockCoverage());
		$mockedCoverageFactory->expects(Invocation::any())->method('createCoverageForDecision')
			->willReturn($this->mockDecisionCoverage());

		return $mockedCoverageFactory;
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockEventDispatcher() {
		$mockedEventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
			->getMock();

		return $mockedEventDispatcher;
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockCoverage() {
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Coverage')->getMock();
	}

	/**
	 * @return DecisionCoverage
	 */
	protected function mockDecisionCoverage() {
		$coverage = $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage')
			->disableOriginalConstructor()->getMock();
		$coverage->expects(Invocation::any())->method('getExpression')->willReturn(
			$this->getMockBuilder('PhpParser\Node\Expr\BinaryOp')->disableOriginalConstructor()
				->setMockClassName('BinaryOp_' . uniqid())->getMock()
		);

		return $coverage;
	}

} 
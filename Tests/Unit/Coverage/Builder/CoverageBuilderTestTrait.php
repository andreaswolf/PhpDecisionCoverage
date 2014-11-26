<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Builder;


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
} 
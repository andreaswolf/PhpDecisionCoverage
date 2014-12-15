<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Builder\DecisionCoverageBuilder;
use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageBuilderEvent;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class DecisionCoverageBuilderTest extends UnitTestCase {
	use CoverageBuilderTestTrait;

	/**
	 * @test
	 */
	public function eventIsRaisedWhenAllPartsHaveBeenCovered() {
		$eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
		$eventDispatcher->expects($this->once())->method('dispatch')->with($this->equalTo('coverage.builder.part.covered'));

		$partBuilders = array(
			$this->mockCoverageBuilder(),
			$this->mockCoverageBuilder(),
		);
		$subject = new DecisionCoverageBuilder($this->mockDecisionCoverage(), $partBuilders, $eventDispatcher);

		$subject->partCoveredHandler(new CoverageBuilderEvent($partBuilders[1]));
		$subject->partCoveredHandler(new CoverageBuilderEvent($partBuilders[0]));
	}

	/**
	 * @test
	 */
	public function eventIsNotRaisedIfPartsNotIncludedInDecisionHaveBeenCovered() {
		$eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
		$eventDispatcher->expects($this->never())->method('dispatch');

		$partBuilders = array(
			$this->mockCoverageBuilder(),
			$this->mockCoverageBuilder(),
		);
		$subject = new DecisionCoverageBuilder($this->mockDecisionCoverage(), $partBuilders, $eventDispatcher);

		$subject->partCoveredHandler(new CoverageBuilderEvent($this->mockCoverageBuilder()));
		$subject->partCoveredHandler(new CoverageBuilderEvent($this->mockCoverageBuilder()));
	}

	/**
	 * @test
	 */
	public function eventIsNotRaisedIfOnePartIsCoveredMultipleTimes() {
		$eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
		$eventDispatcher->expects($this->never())->method('dispatch');

		$partBuilders = array(
			$this->mockCoverageBuilder(),
			$this->mockCoverageBuilder(),
		);
		$subject = new DecisionCoverageBuilder($this->mockDecisionCoverage(), $partBuilders, $eventDispatcher);

		$subject->partCoveredHandler(new CoverageBuilderEvent($partBuilders[0]));
		$subject->partCoveredHandler(new CoverageBuilderEvent($partBuilders[0]));
	}


	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockCoverageBuilder() {
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageBuilder')
			->setMockClassName('CoverageBuilder' . uniqid())->getMock();
	}

}

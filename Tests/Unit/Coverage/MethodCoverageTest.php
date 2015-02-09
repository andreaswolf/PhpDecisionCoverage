<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage;

use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class MethodCoverageTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function feasibleInputsOfOneDecisionAreCorrectlyAggregated() {
		$subject = new MethodCoverage('SomeMethod', 'foo');
		$subject->addDecisionCoverage($this->mockDecisionCoverage(2, 1));

		$this->assertEquals(2, $subject->countFeasibleDecisionInputs());
	}

	/**
	 * @test
	 */
	public function coveredInputsOfOneDecisionAreCorrectlyAggregated() {
		$subject = new MethodCoverage('SomeMethod', 'foo');
		$subject->addDecisionCoverage($this->mockDecisionCoverage(2, 1));

		$this->assertEquals(1, $subject->countCoveredDecisionInputs());
	}

	/**
	 * @test
	 */
	public function coverageOfOneDecisionIsCorrectlyAggregated() {
		$this->markTestSkipped();
		$subject = new MethodCoverage('SomeMethod', 'foo');
		$subject->addDecisionCoverage($this->mockDecisionCoverage(2, 1));

		$this->assertEquals(0.5, $subject->getDecisionCoverage());
	}

	/**
	 * @test
	 */
	public function feasibleInputsOfTwoDecisionsAreCorrectlyAggregated() {
		$subject = new MethodCoverage('SomeMethod', 'foo');
		$subject->addDecisionCoverage($this->mockDecisionCoverage(2, 1));
		$subject->addDecisionCoverage($this->mockDecisionCoverage(4, 3));

		$this->assertEquals(6, $subject->countFeasibleDecisionInputs());
	}

	/**
	 * @test
	 */
	public function coveredInputsOfOfTwoDecisionsAreCorrectlyAggregated() {
		$subject = new MethodCoverage('SomeMethod', 'foo');
		$subject->addDecisionCoverage($this->mockDecisionCoverage(2, 1));
		$subject->addDecisionCoverage($this->mockDecisionCoverage(4, 3));

		$this->assertEquals(4, $subject->countCoveredDecisionInputs());
	}

	/**
	 * @test
	 */
	public function coverageOfTwoDecisionsIsCorrectlyAggregated() {
		$this->markTestSkipped();
		$subject = new MethodCoverage('SomeMethod', 'foo');
		$subject->addDecisionCoverage($this->mockDecisionCoverage(2, 1));
		$subject->addDecisionCoverage($this->mockDecisionCoverage(4, 3));

		$this->assertEquals(0.66, $subject->getDecisionCoverage());
	}



	/**
	 * @param int $feasibleInputs
	 * @param int $coveredInputs
	 * @return DecisionCoverage
	 */
	protected function mockDecisionCoverage($feasibleInputs, $coveredInputs) {
		$mockedDecisionCoverage = $this->getMockBuilder(DecisionCoverage::class)->disableOriginalConstructor()->getMock();
		$mockedDecisionCoverage->expects($this->any())->method('countFeasibleInputs')->willReturn($feasibleInputs);
		$mockedDecisionCoverage->expects($this->any())->method('countUniqueCoveredInputs')->willReturn($coveredInputs);

		return $mockedDecisionCoverage;
	}

}

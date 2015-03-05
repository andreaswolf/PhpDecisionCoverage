<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage;

use AndreasWolf\DecisionCoverage\Coverage\ClassCoverage;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class FileCoverageTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function inputCoverageOfClassIsCorrectlyAggregated() {
		$subject = new FileCoverage('/some/file.php');

		$subject->addCoverage($this->mockClassCoverage(2, 1));

		$this->assertEquals(2, $subject->countFeasibleDecisionInputs());
		$this->assertEquals(1, $subject->countCoveredDecisionInputs());
	}

	/**
	 * @test
	 */
	public function inputCoverageOfMultipleClassesIsCorrectlyAggregated() {
		$subject = new FileCoverage('/some/file.php');

		$subject->addCoverage($this->mockClassCoverage(2, 1));
		$subject->addCoverage($this->mockClassCoverage(7, 4));

		$this->assertEquals(9, $subject->countFeasibleDecisionInputs());
		$this->assertEquals(5, $subject->countCoveredDecisionInputs());
	}

	/**
	 * Tests if a class and some additional code in the same file are correctly aggregated.
	 * @test
	 */
	public function inputCoverageOfClassCombinedWithNormalInputCoverageIsCorrectlyAggregated() {
		$subject = new FileCoverage('/some/file.php');

		$subject->addCoverage($this->mockClassCoverage(2, 1));
		$subject->addInputCoverage($this->mockDecisionCoverage(7, 4));

		$this->assertEquals(9, $subject->countFeasibleDecisionInputs());
		$this->assertEquals(5, $subject->countCoveredDecisionInputs());
	}

	/**
	 * @param int $feasibleInputs
	 * @param int $coveredInputs
	 * @return ClassCoverage
	 */
	protected function mockClassCoverage($feasibleInputs, $coveredInputs) {
		$mockedDecisionCoverage = $this->getMockBuilder(ClassCoverage::class)->disableOriginalConstructor()->getMock();
		$mockedDecisionCoverage->expects($this->any())->method('countFeasibleDecisionInputs')->willReturn($feasibleInputs);
		$mockedDecisionCoverage->expects($this->any())->method('countCoveredDecisionInputs')->willReturn($coveredInputs);

		return $mockedDecisionCoverage;
	}

	/**
	 * @param int $feasibleInputs
	 * @param int $coveredInputs
	 * @return DecisionCoverage
	 */
	protected function mockDecisionCoverage($feasibleInputs, $coveredInputs) {
		$mockedInputCoverage = $this->getMockBuilder(DecisionCoverage::class)->disableOriginalConstructor()->getMock();
		$mockedInputCoverage->expects($this->any())->method('countFeasibleInputs')->willReturn($feasibleInputs);
		$mockedInputCoverage->expects($this->any())->method('countUniqueCoveredInputs')->willReturn($coveredInputs);

		return $mockedInputCoverage;
	}

}

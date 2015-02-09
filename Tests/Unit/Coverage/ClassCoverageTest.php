<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage;

use AndreasWolf\DecisionCoverage\Coverage\ClassCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class ClassCoverageTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function feasibleInputsOfOneMethodAreCorrectlyAggregated() {
		$subject = new ClassCoverage('SomeClass');
		$subject->addMethodCoverage($this->mockMethodCoverage(2, 1));

		$this->assertEquals(2, $subject->countFeasibleDecisionInputs());
	}

	/**
	 * @test
	 */
	public function coveredInputsOfOneMethodAreCorrectlyAggregated() {
		$subject = new ClassCoverage('SomeClass');
		$subject->addMethodCoverage($this->mockMethodCoverage(2, 1));

		$this->assertEquals(1, $subject->countCoveredDecisionInputs());
	}

	/**
	 * @test
	 */
	public function coverageOfOneMethodIsCorrectlyAggregated() {
		$this->markTestSkipped();
		$subject = new ClassCoverage('SomeClass');
		$subject->addMethodCoverage($this->mockMethodCoverage(2, 1));

		$this->assertEquals(0.5, $subject->getDecisionCoverage());
	}

	/**
	 * @test
	 */
	public function feasibleInputsOfTwoMethodsAreCorrectlyAggregated() {
		$subject = new ClassCoverage('SomeClass');
		$subject->addMethodCoverage($this->mockMethodCoverage(2, 1));
		$subject->addMethodCoverage($this->mockMethodCoverage(4, 3));

		$this->assertEquals(6, $subject->countFeasibleDecisionInputs());
	}

	/**
	 * @test
	 */
	public function coveredInputsOfOfTwoMethodsAreCorrectlyAggregated() {
		$subject = new ClassCoverage('SomeClass');
		$subject->addMethodCoverage($this->mockMethodCoverage(2, 1));
		$subject->addMethodCoverage($this->mockMethodCoverage(4, 3));

		$this->assertEquals(4, $subject->countCoveredDecisionInputs());
	}

	/**
	 * @test
	 */
	public function coverageOfTwoMethodsIsCorrectlyAggregated() {
		$this->markTestSkipped();
		$subject = new ClassCoverage('SomeClass');
		$subject->addMethodCoverage($this->mockMethodCoverage(2, 1));
		$subject->addMethodCoverage($this->mockMethodCoverage(4, 3));

		$this->assertEquals(0.66, $subject->getDecisionCoverage());
	}

	/**
	 * @param int $feasibleInputs
	 * @param int $coveredInputs
	 * @return MethodCoverage
	 */
	protected function mockMethodCoverage($feasibleInputs, $coveredInputs) {
		$mockedDecisionCoverage = $this->getMockBuilder(MethodCoverage::class)->disableOriginalConstructor()->getMock();
		$mockedDecisionCoverage->expects($this->any())->method('countFeasibleDecisionInputs')->willReturn($feasibleInputs);
		$mockedDecisionCoverage->expects($this->any())->method('countCoveredDecisionInputs')->willReturn($coveredInputs);

		return $mockedDecisionCoverage;
	}
	
}

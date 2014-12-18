<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Evaluation;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\DecisionSample;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class DecisionSampleTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function decisionInputMatchesIfAllValuesAreEqual() {
		$subject = $this->createDecisionSample(array('A' => FALSE, 'B' => TRUE));

		$this->assertTrue($subject->coversDecisionInput(array('A' => FALSE, 'B' => TRUE)));
	}

	/**
	 * @test
	 */
	public function decisionInputMatchesIfValueIsSkippedIn() {
		$subject = $this->createDecisionSample(array('A' => FALSE, 'B' => TRUE));

		$this->assertTrue($subject->coversDecisionInput(array('A' => FALSE)));
	}

	/**
	 * @test
	 */
	public function decisionInputDoesNotMatchIfOneInputValueDoesNotMatch() {
		$subject = $this->createDecisionSample(array('A' => FALSE, 'B' => TRUE));

		$this->assertFalse($subject->coversDecisionInput(array('A' => TRUE, 'B' => TRUE)));
	}


	/**
	 * @param array $values
	 * @return DecisionSample
	 */
	protected function createDecisionSample($values) {
		$sampleInput = new DecisionInput($values);

		return new DecisionSample($sampleInput, array(), FALSE, $this->mockDataSample());
	}

	/**
	 * @return DataSample
	 */
	protected function mockDataSample() {
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample')
			->disableOriginalConstructor()->getMock();
	}

}

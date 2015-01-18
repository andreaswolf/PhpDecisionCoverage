<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Input;

use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class DecisionInputTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function inputsMatchForEqualVariableValues() {
		$inputA = new DecisionInput(array('A' => TRUE, 'B' => FALSE));
		$inputB = new DecisionInput(array('A' => TRUE, 'B' => FALSE));

		$this->assertTrue($inputA->equalTo($inputB));
	}

	/**
	 * @test
	 */
	public function inputMatchesIfItHasAdditionalValuesSet() {
		$inputA = new DecisionInput(array('A' => TRUE));
		$inputB = new DecisionInput(array('A' => TRUE, 'B' => FALSE));

		$this->assertTrue($inputA->equalTo($inputB));
	}

	/**
	 * @test
	 */
	public function inputDoesNotMatchIfVariableHasDifferentValue() {
		$inputA = new DecisionInput(array('A' => TRUE, 'B' => FALSE));
		$inputB = new DecisionInput(array('A' => FALSE, 'B' => FALSE));

		$this->assertFalse($inputA->equalTo($inputB));
	}

	/**
	 * @test
	 */
	public function inputDoesNotMatchIfVariableIsNotSet() {
		$inputA = new DecisionInput(array('A' => TRUE, 'B' => FALSE));
		$inputB = new DecisionInput(array('A' => TRUE));

		$this->assertFalse($inputA->equalTo($inputB));
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Tests\Functional\Coverage\MCDC;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\DecisionSample;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;


class DecisionCoverageTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function coverageIsZeroByDefault() {
		$expression = new Expr\BinaryOp\BooleanAnd($this->mockSimpleExpression(), $this->mockSimpleExpression());

		$subject = new DecisionCoverage($expression, array('A', 'B'), array(
			new DecisionInput(array('A' => TRUE, 'B' => TRUE)),
			new DecisionInput(array('A' => TRUE, 'B' => FALSE)),
			new DecisionInput(array('A' => FALSE)),
		));

		$this->assertSame(0.0, $subject->getCoverage());
	}

	/**
	 * @test
	 */
	public function coverageForASingleSampleAndOneFeasibleInputIsOne() {
		$expression = new Expr\BinaryOp\BooleanAnd($this->mockSimpleExpression(), $this->mockSimpleExpression());

		$subject = new DecisionCoverage($expression, array('A'), array(
			new DecisionInput(array('A' => TRUE)),
		));
		$decisionSample = array('A' => TRUE);
		$subject->addSample($this->createDecisionSample($decisionSample));

		$this->assertSame(1.0, $subject->getCoverage());
	}

	/**
	 * This test checks if the coverage of short-circuited input combinations is determined correctly.
	 *
	 * For a boolean AND, if the left branch is FALSE, evaluation is aborted and the two possible values TRUE/FALSE
	 * for the right branch are summarized to one don’t care.
	 *
	 * @test
	 */
	public function coverageDoesNotChangeIfAShortCircuitedVariableIsCoveredWithDifferentValues() {
		$expression = new Expr\BinaryOp\BooleanAnd($this->mockSimpleExpression(), $this->mockSimpleExpression());

		$subject = new DecisionCoverage($expression, array('A'), array(
			new DecisionInput(array('A' => FALSE)),
			new DecisionInput(array('A' => TRUE, 'B' => FALSE)),
			new DecisionInput(array('A' => TRUE, 'B' => TRUE)),
		));
		$subject->addSample($this->createDecisionSample(array('A' => FALSE, 'B' => FALSE)));
		$subject->addSample($this->createDecisionSample(array('A' => FALSE, 'B' => TRUE)));

		$this->assertEquals(1 / 3, $subject->getCoverage(), '', 0.01);
	}

	/**
	 * @test
	 */
	public function coverageIsCalculatedCorrectlyForTwoCoveredFeasibleInputsOfBooleanAnd() {
		$expression = new Expr\BinaryOp\BooleanAnd($this->mockSimpleExpression(), $this->mockSimpleExpression());

		$subject = new DecisionCoverage($expression, array('A'), array(
			new DecisionInput(array('A' => FALSE)),
			new DecisionInput(array('A' => TRUE, 'B' => FALSE)),
			new DecisionInput(array('A' => TRUE, 'B' => TRUE)),
		));
		$subject->addSample($this->createDecisionSample(array('A' => FALSE)));
		$subject->addSample($this->createDecisionSample(array('A' => TRUE, 'B' => TRUE)));

		$this->assertEquals(2 / 3, $subject->getCoverage(), '', 0.01);
	}

	/**
	 * @test
	 */
	public function isCoveredReturnsTrueForCoveredInput() {
		$expression = new Expr\BinaryOp\BooleanAnd($this->mockSimpleExpression(), $this->mockSimpleExpression());

		$subject = new DecisionCoverage($expression, array('A'), array(
			new DecisionInput(array('A' => FALSE)),
			new DecisionInput(array('A' => TRUE, 'B' => FALSE)),
			new DecisionInput(array('A' => TRUE, 'B' => TRUE)),
		));
		$subject->addSample($this->createDecisionSample(array('A' => FALSE)));

		$this->assertTrue($subject->isCovered(new DecisionInput(array('A' => FALSE))));
	}

	/**
	 * @test
	 */
	public function isCoveredReturnsTrueIfShortCircuitedVariableHasDifferentValueThanCoveredVariable() {
		$expression = new Expr\BinaryOp\BooleanAnd($this->mockSimpleExpression(), $this->mockSimpleExpression());

		$subject = new DecisionCoverage($expression, array('A'), array(
			new DecisionInput(array('A' => FALSE)),
			new DecisionInput(array('A' => TRUE, 'B' => FALSE)),
			new DecisionInput(array('A' => TRUE, 'B' => TRUE)),
		));
		$subject->addSample($this->createDecisionSample(array('A' => FALSE, 'B' => FALSE)));

		$this->assertTrue($subject->isCovered(new DecisionInput(array('A' => FALSE, 'B' => TRUE))));
	}

	/**
	 * @return Expr
	 */
	public function mockSimpleExpression() {
		return $this->getMockBuilder('PhpParser\Node\Expr')->getMock();
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockDataSample() {
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample')
			->disableOriginalConstructor()->getMock();
	}

	/**
	 * @param array $inputValues
	 * @return DecisionSample
	 */
	protected function createDecisionSample($inputValues) {
		return new DecisionSample(new DecisionInput($inputValues), array(), TRUE, $this->mockDataSample());
	}

}

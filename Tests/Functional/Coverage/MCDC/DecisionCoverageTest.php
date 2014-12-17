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
	 * @test
	 */
	public function booleanAnd() {
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

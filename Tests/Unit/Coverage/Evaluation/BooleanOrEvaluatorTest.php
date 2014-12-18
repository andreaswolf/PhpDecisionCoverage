<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Evaluation;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\BooleanAndEvaluator;
use AndreasWolf\DecisionCoverage\Coverage\Evaluation\BooleanOrEvaluator;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;


class BooleanOrEvaluatorTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function evaluationResultIsFalseIfBothInputsAreFalse() {
		$expression = $this->createBooleanOr('A',
			$this->mockCondition('B'),
			$this->mockCondition('C'));
		$input = $this->createDecisionInput(array('B' => FALSE, 'C' => FALSE));

		$subject = $this->createSubject($expression);
		$result = $subject->evaluate($input);

		$this->assertFalse($result->getValue());
	}

	/**
	 * @test
	 */
	public function resultIsShortCircuitedIfFirstExpressionValueIsTrue() {
		$expression = $this->createBooleanOr('A',
			$this->mockCondition('B'),
			$this->mockCondition('C'));
		$input = $this->createDecisionInput(array('B' => TRUE, 'C' => TRUE));

		$subject = $this->createSubject($expression);
		$result = $subject->evaluate($input);

		$this->assertTrue($result->isShortCircuited());
		$this->assertEquals($expression->left, $result->getLastEvaluatedExpression());
	}

	/**
	 * @test
	 */
	public function resultIsTrueIfBothInputsAreTrue() {
		$expression = $this->createBooleanOr('A',
			$this->mockCondition('B'),
			$this->mockCondition('C'));
		$input = $this->createDecisionInput(array('B' => TRUE, 'C' => TRUE));

		$subject = $this->createSubject($expression);
		$result = $subject->evaluate($input);

		$this->assertTrue($result->getValue());
	}

	/**
	 * @test
	 */
	public function evaluatorIsNotShortedIfRightInputIsTrue() {
		$expression = $this->createBooleanOr('A',
			$this->mockCondition('B'),
			$this->mockCondition('C'));
		$input = $this->createDecisionInput(array('B' => FALSE, 'C' => TRUE));

		$subject = $this->createSubject($expression);
		$result = $subject->evaluate($input);

		$this->assertFalse($result->isShortCircuited());
	}

	/**
	 * @test
	 */
	public function exceptionIsThrownIfRightSubexpressionWasNotEvaluated() {
		$this->setExpectedException('RuntimeException');

		$expression = $this->createBooleanOr('A',
			$this->mockCondition('B'),
			$this->mockCondition('C'));
		$input = $this->createDecisionInput(array('B' => FALSE));

		$subject = $this->createSubject($expression);
		$result = $subject->evaluate($input);
	}

	/**
	 * @return BooleanAndEvaluator
	 */
	protected function createSubject($expression) {
		return new BooleanOrEvaluator($expression);
	}


	protected function createDecisionInput($values) {
		return new DecisionInput($values);
	}

	protected function createBooleanAnd($nodeId, $left, $right) {
		$expression = new Expr\BinaryOp\BooleanAnd($left, $right);
		$expression->setAttribute('coverage__nodeId', $nodeId);

		return $expression;
	}

	protected function createBooleanOr($nodeId, $left, $right) {
		$expression = new Expr\BinaryOp\BooleanOr($left, $right);
		$expression->setAttribute('coverage__nodeId', $nodeId);

		return $expression;
	}

	protected function mockCondition($nodeId) {
		$mock = $this->getMockBuilder('PhpParser\Node\Expr')->getMock();
		$mock->expects($this->any())->method('getSubNodeNames')->willReturn(array());
		$mock->expects($this->any())->method('getAttribute')->with('coverage__nodeId')->willReturn($nodeId);

		return $mock;
	}

}

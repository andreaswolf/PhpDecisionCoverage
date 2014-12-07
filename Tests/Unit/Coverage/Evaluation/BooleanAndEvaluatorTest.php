<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Evaluation;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\Evaluation\BooleanAndEvaluator;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;


class BooleanAndEvaluatorTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function evaluatorIsNotShortedIfNoInputWasInserted() {
		$subject = $this->createSubject();

		$this->assertFalse($subject->isShorted());
	}

	/**
	 * @test
	 */
	public function getOutputThrowsExceptionIfEvaluationWasNotFinished() {
		$this->setExpectedException('InvalidArgumentException', '', 1417909000);
		$subject = $this->createSubject();

		$subject->recordInputValue(new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, TRUE));

		$this->assertTrue($subject->getOutput());
	}

	/**
	 * @test
	 */
	public function evaluatorIsShortedForAFalseInputValue() {
		$subject = $this->createSubject();

		$subject->recordInputValue(new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, FALSE));

		$this->assertTrue($subject->isShorted());
	}

	/**
	 * @test
	 */
	public function evaluatorReturnsTrueForASingleTrueInputValue() {
		$subject = $this->createSubject();

		$subject->recordInputValue(new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, TRUE));
		$subject->finishEvaluation();

		$this->assertTrue($subject->getOutput());
	}

	/**
	 * @return BooleanAndEvaluator
	 */
	protected function createSubject() {
		return new BooleanAndEvaluator(new BooleanAnd(
			$this->getMock('PhpParser\Node\Expr'),
			$this->getMock('PhpParser\Node\Expr')
		));
	}

}

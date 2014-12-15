<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Weighting;

use AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightFactory;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;


class ExpressionWeightFactoryTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function weightReturnedForConditionIsInstanceOfRightClass() {
		$subject = new ExpressionWeightFactory();

		$weight = $subject->createForCondition($this->getMock('PhpParser\Node\Expr'));

		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\Coverage\Weighting\ConditionWeight', $weight);
	}

	/**
	 * @test
	 */
	public function weightObjectForBooleanAndIsInstanceOfRightClass() {
		$subject = new ExpressionWeightFactory();

		$expr = new BooleanAnd($this->getMock('PhpParser\Node\Expr'), $this->getMock('PhpParser\Node\Expr'));

		$weight = $subject->createForDecision($expr, $this->mockWeight(), $this->mockWeight());

		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\Coverage\Weighting\BooleanAndWeight', $weight);
	}

	/**
	 * @test
	 */
	public function creatingDecisionWeightObjectForExpressionThatIsNoDecisionLeadsToFailure() {
		$this->setExpectedException('InvalidArgumentException', '', 1417689417);
		$subject = new ExpressionWeightFactory();

		$expr = $this->getMockBuilder('PhpParser\Node\Expr\BinaryOp')->disableOriginalConstructor()->getMock();

		$weight = $subject->createForDecision($expr, $this->mockWeight(), $this->mockWeight());
	}


	protected function mockWeight() {
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeight')->getMock();
	}

}

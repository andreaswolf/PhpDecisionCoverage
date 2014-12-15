<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis;

use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;


class ProbeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function addedExpressionsCanBeRetrieved(){
		$subject = new Probe(1);

		$expression = $this->mockExpression();
		$subject->addWatchedExpression($expression);

		$this->assertEquals(array($expression), $subject->getWatchedExpressions());
	}

	/**
	 * @test
	 */
	public function addedExpressionsAreCountedCorrectly() {
		$subject = new Probe(1);

		$this->assertEquals(0, $subject->countWatchedExpressions());

		$subject->addWatchedExpression($this->mockExpression());

		$this->assertEquals(1, $subject->countWatchedExpressions());
	}

	/**
	 * @test
	 */
	public function expressionIsNotAddedAgainIfAlreadyPresent() {
		$subject = new Probe(1);

		$expression = $this->mockExpression();
		$subject->addWatchedExpression($expression);
		$subject->addWatchedExpression($expression);

		$this->assertEquals(array($expression), $subject->getWatchedExpressions());
	}

	/**
	 * @return Expr
	 */
	protected function mockExpression() {
		return $this->getMockBuilder('PhpParser\Node\Expr')->disableOriginalConstructor()->getMock();
	}

}

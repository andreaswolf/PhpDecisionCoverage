<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis;

use AndreasWolf\DecisionCoverage\StaticAnalysis\DataCollectionProbe;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;


class ProbeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function addedExpressionsCanBeRetrieved(){
		$subject = new DataCollectionProbe(1);

		$expression = $this->mockExpression();
		$subject->addWatchedExpression($expression);

		$this->assertEquals(array($expression), $subject->getWatchedExpressions());
	}

	/**
	 * @test
	 */
	public function addedExpressionsAreCountedCorrectly() {
		$subject = new DataCollectionProbe(1);

		$this->assertEquals(0, $subject->countWatchedExpressions());

		$subject->addWatchedExpression($this->mockExpression());

		$this->assertEquals(1, $subject->countWatchedExpressions());
	}

	/**
	 * @test
	 */
	public function expressionIsNotAddedAgainIfAlreadyPresent() {
		$subject = new DataCollectionProbe(1);

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

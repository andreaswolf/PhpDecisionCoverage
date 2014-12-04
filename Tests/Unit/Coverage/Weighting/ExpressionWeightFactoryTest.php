<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Weighting;

use AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightFactory;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class ExpressionWeightFactoryTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function weightReturnedForConditionIsInstanceOfRightClass() {
		$subject = new ExpressionWeightFactory();

		$weight = $subject->createForCondition($this->getMock('PhpParser\Node\Expr'));

		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\Coverage\Weighting\ConditionWeight', $weight);
	}

}

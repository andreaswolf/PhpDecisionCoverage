<?php
namespace AndreasWolf\DecisionCoverage\Tests\Acceptance\Fixtures;

use AndreasWolf\DecisionCoverage\Tests\Fixtures\Acceptance\SimpleDecisions;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SimpleDecisionTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @test
	 */
	public function testSingleConditionCoverageWithTrue() {
		$subject = new SimpleDecisions();

		$subject->coverSingleCondition(6);
	}

	/**
	 * @test
	 */
	public function testSingleConditionCoverageWithFalse() {
		$subject = new SimpleDecisions();

		$subject->coverSingleCondition(5);
	}

	/**
	 * @test
	 */
	public function testSimpleDecisionCoverageWithTF() {
		$subject = new SimpleDecisions();

		$subject->coverDecisionWithBooleanAnd('A', 11);
	}

}
 
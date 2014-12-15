<?php
namespace AndreasWolf\DecisionCoverage\Tests\Acceptance\Fixtures;

/**
 * Test fixture for simple decisions
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SimpleDecisions {


	public function coverSingleCondition($shouldBeSix) {
		if ($shouldBeSix === 6) {
			echo "foo";
		}
	}

	public function coverDecisionWithBooleanAnd($foo, $bar) {
		if ($foo == 'A' && $bar < 10) {
			echo "baz";
		}
	}


}

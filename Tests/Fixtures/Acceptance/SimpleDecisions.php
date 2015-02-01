<?php
namespace AndreasWolf\DecisionCoverage\Tests\Fixtures\Acceptance;

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

	public function coverDecisionWithBooleanOrsNestedInBooleanAnd($A, $B, $C, $D) {
		if (($A == TRUE || $B == TRUE) && ($C == TRUE || $D == TRUE)) {
			echo "foo";
		}
	}


}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage;


/**
 * Coverage for a logical AND ("A && B").
 *
 * Due to short-circuit evaluation, this needs three out of four combinations to be full covered:
 * TRUE/TRUE, TRUE/FALSE, FALSE/NOT EVALUATED.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class LogicalAndCoverage extends LogicalExpressionCoverage {

	public function getCoverage() {
		$coverage = 0.0;
		if ($this->isValueCombinationCovered(TRUE, TRUE)) {
			$coverage += 1;
		}
		if ($this->isValueCombinationCovered(TRUE, FALSE)) {
			$coverage += 1;
		}
		// FALSE as left value leads to the second being a don’t care (not evaluated anyways because of short-circuit)
		// boolean logic
		if ($this->isValueCombinationCovered(FALSE, FALSE) || $this->isValueCombinationCovered(FALSE, TRUE)) {
			$coverage += 1;
		}

		// 0.33 + 0.33 + 0.33 != 1…
		return round($coverage / 3, 2);
	}

}

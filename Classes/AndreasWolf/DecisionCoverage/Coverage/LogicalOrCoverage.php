<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

/**
 * Coverage for a logical OR ("A || B").
 *
 * Due to short-circuit evaluation, this needs three out of four combinations to be full covered:
 * FALSE/FALSE, FALSE/TRUE, TRUE/NOT EVALUATED
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class LogicalOrCoverage extends LogicalExpressionCoverage {

	/**
	 * Returns the coverage for this condition as a float.
	 *
	 * @return float The coverage as a percentage (0…1.0)
	 */
	public function getCoverage() {
		$coverage = 0.0;

		if ($this->isValueCombinationCovered(FALSE, FALSE)) {
			$coverage += 1;
		}
		if ($this->isValueCombinationCovered(FALSE, TRUE)) {
			$coverage += 1;
		}
		// TRUE as left value leads to the second being a don’t care (not evaluated anyways because of short-circuit)
		// boolean logic
		if ($this->isValueCombinationCovered(TRUE, FALSE) || $this->isValueCombinationCovered(TRUE, TRUE)) {
			$coverage += 1;
		}

		// 0.33 + 0.33 + 0.33 != 1…
		return round($coverage / 3, 2);
	}

}
 
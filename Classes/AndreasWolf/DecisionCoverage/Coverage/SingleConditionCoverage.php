<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use PhpParser\Node\Expr;


/**
 * A coverage metric for a single boolean condition (e.g. $foo == 'bar').
 *
 * This effectively can have two values covered, TRUE and FALSE.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SingleConditionCoverage extends ExpressionCoverage implements InputCoverage {

	public function countFeasibleInputs() {
		return 2;
	}

	public function countUniqueCoveredInputs() {
		return (int)($this->getCoverage() * 2);
	}

	/**
	 * @param bool $value
	 * @return bool
	 */
	public function isValueCovered($value) {
		return in_array($value, $this->coveredValues);
	}

	/**
	 * Returns the coverage for this condition as a float.
	 *
	 * @return float The coverage as a percentage (0…1.0)
	 */
	public function getCoverage() {
		$coverage = 0.0;
		if ($this->isValueCovered(TRUE)) {
			$coverage += 0.5;
		}
		if ($this->isValueCovered(FALSE)) {
			$coverage += 0.5;
		}

		return $coverage;
	}

}

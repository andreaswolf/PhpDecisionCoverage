<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use PhpParser\Node\Expr;


/**
 * A coverage metric for a single boolean condition (e.g. $foo == 'bar').
 *
 * This effectively can have two values covered, TRUE and FALSE.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SingleConditionCoverage {

	/**
	 * @var Expr
	 */
	protected $expression;

	/**
	 * @var array
	 */
	protected $coveredValues = array();

	/**
	 * @param Expr $expression The covered expression
	 */
	public function __construct(Expr $expression) {
		$this->expression = $expression;
	}

	/**
	 * @param boolean $value
	 */
	public function recordCoveredValue($value) {
		$this->coveredValues[] = $value;
	}

	/**
	 * Returns the coverage for this condition as a float.
	 *
	 * @return float The coverage as a percentage (0…1.0)
	 */
	public function getCoverage() {
		$coverage = 0.0;
		if (in_array(TRUE, $this->coveredValues)) {
			$coverage += 0.5;
		}
		if (in_array(FALSE, $this->coveredValues)) {
			$coverage += 0.5;
		}

		return $coverage;
	}

}
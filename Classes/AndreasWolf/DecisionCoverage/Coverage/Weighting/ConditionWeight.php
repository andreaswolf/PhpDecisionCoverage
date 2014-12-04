<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Weighting;

/**
 * The weight of an (uncoupled) condition.
 *
 * This returns static values because an uncoupled condition can always only have TRUE and FALSE as input values and
 * outcomes.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ConditionWeight implements ExpressionWeight {

	public function getValue() {
		return 2;
	}

	public function getTrueValue() {
		return 1;
	}

	public function getFalseValue() {
		return 1;
	}

}

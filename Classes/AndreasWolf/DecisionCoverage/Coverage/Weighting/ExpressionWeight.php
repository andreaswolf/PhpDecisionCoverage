<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Weighting;

/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface ExpressionWeight {

	public function getValue();

	public function getTrueValue();

	public function getFalseValue();

}

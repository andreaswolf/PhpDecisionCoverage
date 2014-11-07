<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\BreakpointDataSet;


/**
 * Coverage for a condition consisting of multiple conditions
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface CompoundConditionCoverage {

	/**
	 * Records an input set for this condition.
	 *
	 * @param BreakpointDataSet $dataSet
	 * @return void
	 */
	public function recordCoveredInput(BreakpointDataSet $dataSet);

	/**
	 * Returns the coverage for this condition as a float.
	 *
	 * @return float The coverage as a percentage (0…1.0)
	 */
	public function getCoverage();

}

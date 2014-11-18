<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;


/**
 * Coverage for a condition consisting of multiple conditions
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface CompoundConditionCoverage {

	/**
	 * Records an input set for this condition.
	 *
	 * @param DataSample $dataSet
	 * @return void
	 */
	public function recordCoveredInput(DataSample $dataSet);

	/**
	 * Returns the coverage for this condition as a float.
	 *
	 * @return float The coverage as a percentage (0…1.0)
	 */
	public function getCoverage();

}

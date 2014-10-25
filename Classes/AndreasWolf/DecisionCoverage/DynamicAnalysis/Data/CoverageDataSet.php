<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

/**
 * A set of data collected at program runtime.
 *
 * This consists of many breakpoint data sets that were triggered
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageDataSet {

	/**
	 * @var BreakpointDataSet[]
	 */
	protected $breakpointData = array();


	public function addBreakpointDataSet(BreakpointDataSet $dataSet) {
		$this->breakpointData[] = $dataSet;
	}

}

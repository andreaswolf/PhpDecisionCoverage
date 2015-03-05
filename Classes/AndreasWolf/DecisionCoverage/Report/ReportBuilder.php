<?php
namespace AndreasWolf\DecisionCoverage\Report;

use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;


interface ReportBuilder {

	/**
	 * Builds a report for the given coverage set
	 *
	 * @param CoverageSet $set
	 * @return void
	 */
	public function build(CoverageSet $set);

	/**
	 * Finishes and saves the generated report
	 *
	 * @return void
	 */
	public function finish();

}

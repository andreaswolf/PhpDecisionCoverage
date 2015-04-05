<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;


/**
 * An aggregate of several coverages
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface CoverageAggregate {

	/**
	 * @return int
	 */
	public function countFeasibleDecisionInputs();

	/**
	 * @return int
	 */
	public function countCoveredDecisionInputs();

	/**
	 * @return float
	 */
	public function getDecisionCoverage();

	/**
	 * @return int
	 */
	public function countTotalEntryPoints();

	/**
	 * @return int
	 */
	public function countCoveredEntryPoints();

}

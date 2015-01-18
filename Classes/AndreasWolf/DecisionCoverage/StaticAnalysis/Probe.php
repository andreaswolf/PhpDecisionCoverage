<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;

/**
 * A data collection point where various values should be watched.
 *
 * Is attached to a syntax tree node.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface Probe {

	/**
	 * @return int
	 */
	public function getLine();

}

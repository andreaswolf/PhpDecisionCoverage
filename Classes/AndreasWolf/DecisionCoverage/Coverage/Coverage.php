<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;


/**
 * Generic interface for all coverage classes.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface Coverage {

	/**
	 * @param ExpressionValue $value
	 * @return boolean The expression value for the given input data set
	 */
	public function recordCoveredValue(ExpressionValue $value);

	/**
	 * @return float The coverage as a value between 0 and 1.
	 */
	public function getCoverage();

}

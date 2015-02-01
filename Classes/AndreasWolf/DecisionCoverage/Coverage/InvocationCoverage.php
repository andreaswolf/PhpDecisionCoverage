<?php
namespace AndreasWolf\DecisionCoverage\Coverage;


use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;


/**
 * A simple coverage to just count the invocations of a statement.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class InvocationCoverage {

	protected $invocations = 0;


	public function record() {
		++$this->invocations;
	}

	/**
	 * @return float The coverage as a value between 0 and 1.
	 */
	public function getCoverage() {
		return ($this->invocations > 0) ? 1.0 : 0.0;
	}

}

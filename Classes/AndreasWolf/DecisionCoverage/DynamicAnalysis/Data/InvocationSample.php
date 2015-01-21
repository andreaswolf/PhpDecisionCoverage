<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\Test;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;


/**
 * A data sample create during the execution of a certain statement.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class InvocationSample implements Sample {

	/**
	 * @var Probe
	 */
	protected $probe;

	/**
	 * @var Test
	 */
	protected $test;

	public function __construct(Probe $probe) {
		$this->probe = $probe;
	}

	/**
	 * @return Probe
	 */
	public function getProbe() {
		return $this->probe;
	}

	/**
	 * @param Test $test
	 */
	public function setTest(Test $test) {
		$this->test = $test;
	}

	/**
	 * @return Test
	 */
	public function getTest() {
		return $this->test;
	}

	// This sample does not need to capture any values; its mere presence in a data set is enough to prove that the
	// watched statement was invoked.

}

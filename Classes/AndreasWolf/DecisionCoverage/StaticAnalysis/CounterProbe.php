<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;


/**
 * Probe that counts each invocation.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class CounterProbe implements Probe {

	/**
	 * @var int
	 */
	protected $invocations = 0;

	protected $line;


	public function __construct($line) {
		$this->line = $line;
	}

	/**
	 * @return mixed
	 */
	public function getLine() {
		return $this->line;
	}

	public function countInvocation() {
		++$this->invocations;
	}

	/**
	 * @return int
	 */
	public function getInvocations() {
		return $this->invocations;
	}

}

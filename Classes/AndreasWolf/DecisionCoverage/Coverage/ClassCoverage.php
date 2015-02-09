<?php
namespace AndreasWolf\DecisionCoverage\Coverage;


class ClassCoverage implements CoverageAggregate {

	/** @var string */
	protected $name;

	/** @var MethodCoverage[] */
	protected $methodCoverages = [];


	public function __construct($name) {
		$this->name = $name;
	}

	public function addMethodCoverage(MethodCoverage $coverage) {
		$this->methodCoverages[] = $coverage;
	}

	public function getMethodCoverages() {
		return $this->methodCoverages;
	}

	/**
	 * @return int
	 */
	public function countFeasibleDecisionInputs() {
		$inputs = 0;
		foreach ($this->methodCoverages as $coverage) {
			$inputs += $coverage->countFeasibleDecisionInputs();
		}
		return $inputs;
	}

	/**
	 * @return int
	 */
	public function countCoveredDecisionInputs() {
		$inputs = 0;
		foreach ($this->methodCoverages as $coverage) {
			$inputs += $coverage->countCoveredDecisionInputs();
		}
		return $inputs;
	}

	/**
	 * @return float
	 */
	public function getDecisionCoverage() {
		// TODO: Implement getDecisionCoverage() method.
	}


}

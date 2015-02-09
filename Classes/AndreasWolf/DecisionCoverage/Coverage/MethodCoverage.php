<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;


/**
 * Coverage for a method, either within a class or in the global scope.
 *
 * This aggregates the coverage of all statements within the method.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class MethodCoverage implements CoverageAggregate {

	/**
	 * @var string
	 */
	protected $methodName;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var InvocationCoverage
	 */
	protected $entryPointCoverage;

	/**
	 * @var DecisionCoverage[]
	 */
	protected $decisionCoverages = array();


	public function __construct($methodName, $methodNodeId) {
		$this->methodName = $methodName;
		$this->id = $methodNodeId;

		$this->entryPointCoverage = new InvocationCoverage();
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getMethodName() {
		return $this->methodName;
	}

	/**
	 * @param DecisionCoverage $coverage
	 */
	public function addDecisionCoverage(DecisionCoverage $coverage) {
		$this->decisionCoverages[] = $coverage;
	}

	/**
	 * @return int
	 */
	public function countFeasibleDecisionInputs() {
		$inputCount = 0;
		foreach ($this->decisionCoverages as $coverage) {
			$inputCount += $coverage->countFeasibleInputs();
		}
		return $inputCount;
	}

	/**
	 * @return int
	 */
	public function countCoveredDecisionInputs() {
		$inputCount = 0;
		foreach ($this->decisionCoverages as $coverage) {
			$inputCount += $coverage->countUniqueCoveredInputs();
		}
		return $inputCount;
	}

	/**
	 * @return float
	 */
	public function getDecisionCoverage() {
		// TODO implement
	}

	/**
	 * @return void
	 */
	public function recordMethodEntry() {
		$this->entryPointCoverage->record();
	}

	/**
	 * @return float
	 */
	public function getEntryPointCoverage() {
		return $this->entryPointCoverage->getCoverage();
	}

}

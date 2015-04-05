<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use PhpParser\Node\Stmt;


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

	/** @var Stmt\ClassMethod|Stmt\Function_ */
	protected $methodNode;

	/**
	 * @var InvocationCoverage
	 */
	protected $entryPointCoverage;

	/**
	 * @var InputCoverage[]
	 */
	protected $inputCoverages = array();


	public function __construct(Stmt $methodStatement) {
		/** @var Stmt\ClassMethod|Stmt\Function_ $methodStatement */
		$this->methodNode = $methodStatement;
		$this->methodName = $methodStatement->name;
		$this->id = $methodStatement->getAttribute('coverage__nodeId');

		$this->entryPointCoverage = new InvocationCoverage();
	}

	public function getNode() {
		return $this->methodNode;
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
	 * @param InputCoverage $coverage
	 */
	public function addInputCoverage(InputCoverage $coverage) {
		$this->inputCoverages[] = $coverage;
	}

	public function getDecisionCoverages() {
		return $this->inputCoverages;
	}

	public function getInputCoverages() {
		return $this->inputCoverages;
	}

	/**
	 * @return int
	 */
	public function countFeasibleDecisionInputs() {
		$inputCount = 0;
		foreach ($this->inputCoverages as $coverage) {
			$inputCount += $coverage->countFeasibleInputs();
		}
		return $inputCount;
	}

	/**
	 * @return int
	 */
	public function countCoveredDecisionInputs() {
		$inputCount = 0;
		foreach ($this->inputCoverages as $coverage) {
			$inputCount += $coverage->countUniqueCoveredInputs();
		}
		return $inputCount;
	}

	/**
	 * @return float
	 */
	public function getDecisionCoverage() {
		if ($this->countFeasibleDecisionInputs() == 0) {
			return 0;
		}
		return $this->countCoveredDecisionInputs() / $this->countFeasibleDecisionInputs();
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

	/**
	 * @return int
	 */
	public function countTotalEntryPoints() {
		return 1;
	}

	/**
	 * @return int
	 */
	public function countCoveredEntryPoints() {
		// Just check if the entry point was invoked at least once
		return $this->entryPointCoverage->countInvocations() > 0;
	}

}

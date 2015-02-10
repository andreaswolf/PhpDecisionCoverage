<?php
namespace AndreasWolf\DecisionCoverage\Coverage;


use PhpParser\Node\Stmt\Class_;


class ClassCoverage implements CoverageAggregate {

	/** @var Class_ */
	protected $classNode;

	/** @var string */
	protected $className;

	/** @var string */
	protected $id;

	/** @var MethodCoverage[] */
	protected $methodCoverages = [];


	public function __construct(Class_ $classStatement) {
		$this->classNode = $classStatement;
		$this->className = $classStatement->name;
		$this->id = $classStatement->getAttribute('coverage__nodeId');
	}

	/**
	 * @return string
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
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

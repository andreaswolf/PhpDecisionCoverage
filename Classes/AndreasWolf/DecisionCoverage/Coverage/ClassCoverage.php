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
	 * Returns the total number of (method) entry points, i.e. the method count.
	 *
	 * @return int
	 */
	public function countTotalEntryPoints() {
		// we cannot simply count the methods, as some methods might be excluded from the coverage (in the future)
		return count($this->methodCoverages);
	}

	/**
	 * Returns the number of (method) entry points in the class that were covered.
	 *
	 * @return int
	 */
	public function countCoveredEntryPoints() {
		$methodCoveragesWithCoveredEntryPoint = array_filter($this->methodCoverages, function($methodCoverage) {
			/** @var MethodCoverage $methodCoverage */
			return $methodCoverage->getEntryPointCoverage() > 0;
		});

		return count($methodCoveragesWithCoveredEntryPoint);
	}

	/**
	 * @return float
	 */
	public function getDecisionCoverage() {
		// TODO: Implement getDecisionCoverage() method.
	}


}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;


/**
 * Coverage for a method, either within a class or in the global scope.
 *
 * This aggregates the coverage of all statements within the class. In the future, it might also hold special coverage
 * objects for the entry/exit points.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class MethodCoverage implements CoverageAggregate, Coverage {

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
	 * @var Coverage[]
	 */
	protected $coverages = array();


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
	 * @param Coverage $coverage
	 */
	public function addCoverage(Coverage $coverage) {
		$this->coverages[] = $coverage;
	}

	/**
	 * @return Coverage[]
	 */
	public function getCoverages() {
		return $this->coverages;
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
	 * @param ExpressionValue $value
	 * @return boolean The expression value for the given input data set
	 * TODO move this to a derived interface, e.g. ValueCoverage
	 */
	public function recordCoveredValue(ExpressionValue $value) {
		throw new \BadMethodCallException('Not implemented');
	}

	/**
	 * @return float The coverage as a value between 0 and 1.
	 */
	public function getCoverage() {
		throw new \BadMethodCallException('Not implemented');
	}

}

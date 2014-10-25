<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit;

/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class Test {

	/**
	 * @var string
	 */
	protected $testClass;

	/**
	 * @var string
	 */
	protected $testName;


	public function __construct($testClass, $testName) {
		$this->testClass = $testClass;
		$this->testName = $testName;
	}

	public function __toString() {
		return $this->testClass . '::' . $this->testName;
	}

}

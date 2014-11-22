<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\Test;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;


/**
 * A set of data collected at program runtime.
 *
 * This consists of many breakpoint data sets that were collected when the respective breakpoints were hit.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageDataSet {

	/**
	 * The analysed code this fetched data is based on.
	 *
	 * @var ResultSet
	 */
	protected $codeAnalysis;

	/**
	 * @var DataSample[]
	 */
	protected $samples = array();

	/**
	 * @var Test
	 */
	protected $currentTest;


	public function __construct(ResultSet $analysisResults) {
		$this->codeAnalysis = $analysisResults;
	}

	public function addSample(DataSample $dataSet) {
		$this->samples[] = $dataSet;
		$dataSet->setTest($this->currentTest);
	}

	/**
	 * @return DataSample[]
	 */
	public function getSamples() {
		return $this->samples;
	}

	/**
	 * @param Test $test
	 */
	public function enterTest($test) {
		$this->currentTest = $test;
	}

	/**
	 * @param Test $test
	 */
	public function exitTest($test) {
		// TODO check if $test == $this->currentTest
		$this->currentTest = NULL;
	}

}

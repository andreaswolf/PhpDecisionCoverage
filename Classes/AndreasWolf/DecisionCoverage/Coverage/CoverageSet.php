<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;
use VirtualFileSystem\Structure\File;


/**
 * Calculated coverage for a set of expressions.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageSet {

	/**
	 * @var CoverageDataSet
	 */
	protected $dataSet;

	/**
	 * @var Coverage[]
	 */
	protected $coveredFiles = array();


	public function __construct(CoverageDataSet $dataSet) {
		$this->dataSet = $dataSet;
	}

	/**
	 * @return CoverageDataSet
	 */
	public function getDataSet() {
		return $this->dataSet;
	}

	/**
	 * @return ResultSet
	 */
	public function getAnalysisResult() {
		return $this->dataSet->getAnalysisResult();
	}

	/**
	 * @param FileCoverage $coverage
	 */
	public function add(FileCoverage $coverage) {
		$this->coveredFiles[$coverage->getFilePath()] = $coverage;
	}

	/**
	 * @param string $path
	 * @return FileCoverage
	 */
	public function getByPath($path) {
		return $this->coveredFiles[$path];
	}

	/**
	 * Returns all files coverages stored in this set.
	 *
	 * @return FileCoverage[]
	 */
	public function getAll() {
		return $this->coveredFiles;
	}

}

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
class CoverageSet implements CoverageAggregate {

	/**
	 * @var CoverageDataSet
	 */
	protected $dataSet;

	/**
	 * @var FileCoverage[]
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

	/**
	 * @return int
	 */
	public function countFeasibleDecisionInputs() {
		$inputs = 0;
		foreach ($this->coveredFiles as $fileCoverage) {
			$inputs += $fileCoverage->countFeasibleDecisionInputs();
		}
		return $inputs;
	}

	/**
	 * @return int
	 */
	public function countCoveredDecisionInputs() {
		$inputs = 0;
		foreach ($this->coveredFiles as $fileCoverage) {
			$inputs += $fileCoverage->countCoveredDecisionInputs();
		}
		return $inputs;
	}

	/**
	 * Returns the total number of (method) entry points, i.e. the method count.
	 *
	 * @return int
	 */
	public function countTotalEntryPoints() {
		return array_reduce($this->coveredFiles, function($count, $currentItem) {
			if ($currentItem instanceof CoverageAggregate) {
				$count += $currentItem->countTotalEntryPoints();
			}
			return $count;
		});
	}

	/**
	 * Returns the number of (method) entry points in the class that were covered.
	 *
	 * @return int
	 */
	public function countCoveredEntryPoints() {
		return array_reduce($this->coveredFiles, function($count, $currentItem) {
			if ($currentItem instanceof CoverageAggregate) {
				$count += $currentItem->countCoveredEntryPoints();
			}
			return $count;
		});
	}

	/**
	 * @return float
	 */
	public function getDecisionCoverage() {
		// TODO: Implement getDecisionCoverage() method.
	}

}

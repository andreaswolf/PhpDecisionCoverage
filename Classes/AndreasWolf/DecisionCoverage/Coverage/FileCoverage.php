<?php
namespace AndreasWolf\DecisionCoverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class FileCoverage implements CoverageAggregate {

	/**
	 * @var string
	 */
	protected $filePath;

	/**
	 * @var CoverageAggregate[]
	 */
	protected $coverages = [];


	public function __construct($filePath) {
		$this->filePath = $filePath;
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}

	public function addInputCoverage(InputCoverage $coverage) {
		$this->coverages[] = $coverage;
	}

	public function addCoverage(CoverageAggregate $coverage) {
		$this->coverages[] = $coverage;
	}

	/**
	 * @return Coverage[]
	 */
	public function getCoverages() {
		return $this->coverages;
	}

	/**
	 * @return float The coverage as a value between 0 and 1.
	 */
	public function getCoverage() {
		// TODO: Implement getCoverage() method.
	}

	/**
	 * @return int
	 */
	public function countFeasibleDecisionInputs() {
		$totalInputs = 0;
		foreach ($this->getCoverages() as $coverage) {
			if ($coverage instanceof CoverageAggregate) {
				$totalInputs += $coverage->countFeasibleDecisionInputs();
			} elseif ($coverage instanceof InputCoverage) {
				$totalInputs += $coverage->countFeasibleInputs();
			}
		}

		return $totalInputs;
	}

	/**
	 * @return int
	 */
	public function countCoveredDecisionInputs() {
		$coveredInputs = 0;
		foreach ($this->getCoverages() as $coverage) {
			if ($coverage instanceof CoverageAggregate) {
				$coveredInputs += $coverage->countCoveredDecisionInputs();
			} elseif ($coverage instanceof InputCoverage) {
				$coveredInputs += $coverage->countUniqueCoveredInputs();
			}

		}

		return $coveredInputs;
	}

	/**
	 * @return float
	 */
	public function getDecisionCoverage() {
		// TODO: Implement getDecisionCoverage() method.
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DecisionCoverage\Source\SourceFile;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 *
 * TODO make Coverage interface usable for this
 */
class FileCoverage implements CoverageAggregate {

	/**
	 * @var string
	 */
	protected $filePath;

	/**
	 * @var Coverage[]
	 */
	protected $coverages;


	public function __construct($filePath) {
		$this->filePath = $filePath;
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}

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
	 * @return float The coverage as a value between 0 and 1.
	 */
	public function getCoverage() {
		// TODO: Implement getCoverage() method.
	}

}

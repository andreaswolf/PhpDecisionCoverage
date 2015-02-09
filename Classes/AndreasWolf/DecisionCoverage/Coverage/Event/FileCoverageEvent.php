<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Event;

use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use Symfony\Component\EventDispatcher\Event;


class FileCoverageEvent extends Event {

	/**
	 * @var $fileCoverage
	 */
	protected $fileCoverage;

	public function __construct(FileCoverage $fileCoverage) {
		$this->fileCoverage = $fileCoverage;
	}

	/**
	 * @return FileCoverage
	 */
	public function getFileCoverage() {
		return $this->fileCoverage;
	}

}

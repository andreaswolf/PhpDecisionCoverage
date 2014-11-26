<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Event;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;


class CoverageEvent {

	/**
	 * @var Coverage
	 */
	protected $coverage;


	public function __construct(Coverage $coverage) {
		$this->coverage = $coverage;
	}

	/**
	 * @return Coverage
	 */
	public function getCoverage() {
		return $this->coverage;
	}

}
 
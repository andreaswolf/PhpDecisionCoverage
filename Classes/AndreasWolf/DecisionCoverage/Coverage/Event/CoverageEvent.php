<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Event;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use Symfony\Component\EventDispatcher\Event;


class CoverageEvent extends Event {

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
 
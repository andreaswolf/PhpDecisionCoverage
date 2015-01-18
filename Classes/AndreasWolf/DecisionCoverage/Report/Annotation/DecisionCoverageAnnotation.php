<?php
namespace AndreasWolf\DecisionCoverage\Report\Annotation;

use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;


class DecisionCoverageAnnotation {

	/**
	 * @var DecisionCoverage
	 */
	protected $coverage;


	public function __construct(DecisionCoverage $coverage) {
		$this->coverage = $coverage;
	}

	/**
	 * @return DecisionCoverage
	 */
	public function getCoverage() {
		return $this->coverage;
	}

}

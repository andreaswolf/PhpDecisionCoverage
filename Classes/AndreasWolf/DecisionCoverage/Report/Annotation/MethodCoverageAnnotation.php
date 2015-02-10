<?php
namespace AndreasWolf\DecisionCoverage\Report\Annotation;

use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;


class MethodCoverageAnnotation {

	/** @var MethodCoverage */
	protected $coverage;


	public function __construct(MethodCoverage $coverage) {
		$this->coverage = $coverage;
	}

	/**
	 * @return MethodCoverage
	 */
	public function getCoverage() {
		return $this->coverage;
	}

}

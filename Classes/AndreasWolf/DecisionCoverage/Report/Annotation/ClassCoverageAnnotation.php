<?php
namespace AndreasWolf\DecisionCoverage\Report\Annotation;

use AndreasWolf\DecisionCoverage\Coverage\ClassCoverage;


class ClassCoverageAnnotation {

	/** @var ClassCoverage */
	protected $coverage;

	public function __construct(ClassCoverage $coverage) {
		$this->coverage = $coverage;
	}

	/**
	 * @return ClassCoverage
	 */
	public function getCoverage() {
		return $this->coverage;
	}

}

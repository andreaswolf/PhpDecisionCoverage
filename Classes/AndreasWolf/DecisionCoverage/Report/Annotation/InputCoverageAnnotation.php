<?php
namespace AndreasWolf\DecisionCoverage\Report\Annotation;

use AndreasWolf\DecisionCoverage\Coverage\InputCoverage;


class InputCoverageAnnotation {

	/**
	 * @var InputCoverage
	 */
	protected $coverage;


	public function __construct(InputCoverage $coverage) {
		$this->coverage = $coverage;
	}

	/**
	 * @return InputCoverage
	 */
	public function getCoverage() {
		return $this->coverage;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;


interface CoverageBuilder {

	/**
	 * Returns the coverage object this builder is building.
	 * @return Coverage
	 */
	public function getCoverage();

} 
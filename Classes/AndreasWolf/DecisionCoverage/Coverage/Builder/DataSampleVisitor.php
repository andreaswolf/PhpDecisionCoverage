<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;


/**
 * Interface for coverage builders that enables to
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface DataSampleVisitor {

	/**
	 * Called before the last data sample is traversed
	 *
	 * @return void
	 */
	public function startTraversal();

	/**
	 * Called when the last data sample has been traversed.
	 *
	 * @return Coverage
	 */
	public function endTraversal();

	/**
	 * Called for each data sample.
	 *
	 * @param DataSample $sample
	 * @return void
	 */
	public function handleSample(DataSample $sample);

}
 
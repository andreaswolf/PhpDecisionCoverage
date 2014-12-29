<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

/**
 * An aggregate of several coverages
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface CoverageAggregate {

	public function addCoverage(Coverage $coverage);

	/**
	 * Returns all the
	 *
	 * @return mixed
	 * TODO rename this when we have found a name for the parts of an aggregate
	 */
	public function getCoverages();

}

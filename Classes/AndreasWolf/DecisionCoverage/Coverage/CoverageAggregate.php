<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

/**
 * An aggregate of several coverages
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface CoverageAggregate {

	public function addCoverage(Coverage $coverage);

}
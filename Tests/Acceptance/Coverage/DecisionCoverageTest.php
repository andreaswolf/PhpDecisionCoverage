<?php
namespace AndreasWolf\DecisionCoverage\Tests\Acceptance\Coverage;

use AndreasWolf\DecisionCoverage\Tests\Acceptance\AcceptanceTestCase;


/**
 * End-to-end acceptance test to get coverage for decisions.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DecisionCoverageTest extends AcceptanceTestCase {

	/**
	 * @test
	 */
	public function coverageForSingleTestRunIsCorrectlyCalculated() {
		$results = $this->runTestAndCollectResults('testSimpleDecisionCoverageWithTF');
	}

}

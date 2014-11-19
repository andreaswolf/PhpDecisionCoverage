<?php
namespace AndreasWolf\DecisionCoverage\Tests\Acceptance;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
abstract class AcceptanceTestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * @var TestRunner
	 */
	protected $testRunHelper;


	/**
	 * Runs the given test and returns the results as a deserialized array.
	 *
	 * @param string $testName
	 * @return CoverageDataSet The data gathered from the test
	 */
	protected function runTestAndCollectResults($testName) {
		$helper = new TestRunner();

		return $helper->runTestAndReturnCoverage($testName);
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Tests\Acceptance;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;


/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class TestRunner {

	/**
	 * The path to the analysis file
	 *
	 * @var string
	 */
	protected $analysisFile;

	/**
	 * The path to the coverage data file
	 *
	 * @var string
	 */
	protected $coverageDataFile;

	/**
	 * The path to the coverage file
	 *
	 * @var string
	 */
	protected $coverageFile;

	/**
	 * @var array
	 */
	protected $filesToDeleteOnShutdown = array();

	public function __construct() {
		$this->scriptFile = realpath(__DIR__ . '/../../Scripts/DecisionCoverage.php');
	}

	/**
	 * Runs the given test from the `Fixtures` folder and returns the coverage object
	 * as a result.
	 *
	 * @param string $testName
	 * @return CoverageDataSet
	 */
	public function runTestAndReturnCoverage($testName) {
		$this->analysisFile = tempnam(sys_get_temp_dir(), 'coverage-test_analysis-');
		$this->runAnalysis();

		$this->coverageDataFile = tempnam(sys_get_temp_dir(), 'coverage-test_data-');
		$this->runTest($testName);

		$this->coverageFile = tempnam(sys_get_temp_dir(), 'coverage-test_coverage-');
		$this->runCoverageBuild();

		$contents = unserialize(file_get_contents($this->coverageFile));

		return $contents;
	}

	/**
	 * @return void
	 */
	protected function runCoverageBuild() {
		$runArguments = array(
			'build',
			$this->coverageDataFile,
			'--output', $this->coverageFile,
		);

		$process = $this->runDecisionCoverageScript($runArguments);
	}

	/**
	 * @param string $testName
	 * @return void
	 */
	protected function runTest($testName) {
		$testsPath = realpath(__DIR__ . '/Fixtures/') . '/';

		$phpunitArguments = '--filter ' . $testName . ' ' . $testsPath;
		$runArguments = array(
			'run',
			$this->analysisFile,
			'--output', $this->coverageDataFile,
			// we don’t need to wrap the PHPUnit arguments here again, doing so will let PHPUnit treat the whole
			// arguments line as one single
			'--phpunit-arguments=' . str_replace('"', '\\"', $phpunitArguments),
		);

		$process = $this->runDecisionCoverageScript($runArguments);
	}

	protected function runAnalysis() {
		$analysisArguments = $this->buildAnalysisArguments();
		$this->filesToDeleteOnShutdown[] = $this->analysisFile;

		$this->runDecisionCoverageScript($analysisArguments);
	}

	/**
	 * Returns the command line arguments for the analysis run, i.e. for the part that checks the code that is to
	 * be executed in the tests (not the tests itself!)
	 *
	 * @return array
	 */
	protected function buildAnalysisArguments() {
		$fixturesPath = realpath(__DIR__ . '/../Fixtures/Acceptance/');

		$analysisArguments = array(
			'analyze',
			'--project=DecisionCoverageAcceptanceTests',
			'--output=' . $this->analysisFile,
			$fixturesPath,
		);

		return $analysisArguments;
	}

	/**
	 * Helper method to run the decision coverage application script.
	 *
	 * @param array $arguments
	 * @return Process
	 */
	protected function runDecisionCoverageScript($arguments) {
		// Symfony\Process expects the arguments to be the first argument
		$processArguments = array_merge(
			array('/usr/bin/env', 'php', $this->scriptFile),
			$arguments
		);

		$process = ProcessBuilder::create($processArguments)->getProcess();

		return $process->mustRun();
	}

}

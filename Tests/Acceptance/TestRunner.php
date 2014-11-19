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
		$this->analysisFile = tempnam(sys_get_temp_dir(), 'coverage-test_analysis-') . '.analysis';
		$this->runAnalysis();

		$this->coverageFile = tempnam(sys_get_temp_dir(), 'coverage-test_coverage-') . '.coverage';
		$this->runTest($testName);

		$contents = unserialize(file_get_contents($this->coverageFile));

		return $contents;
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
			'--output', $this->coverageFile,
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
	 * @return array
	 */
	protected function buildAnalysisArguments() {
		$testsPath = realpath(__DIR__ . '/Fixtures/');

		$analysisArguments = array(
			'analyze',
			'--project=DecisionCoverageAcceptanceTests',
			'--output=' . $this->analysisFile,
			$testsPath,
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

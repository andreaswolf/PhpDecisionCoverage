<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit;

use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;
use Exception;
use PHPUnit_Framework_AssertionFailedError;


/**
 * Listener for PHPUnit tests.
 *
 * This runs in the PHPUnit process that is spawned by the ClientEventSubscriber instance.
 * Reports test progress back to the data collector process via the FIFO queue.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TestListener extends \PHPUnit_Framework_BaseTestListener {

	/**
	 * @var resource
	 */
	protected $fifoHandle;

	/**
	 * @param $file
	 */
	public function __construct($file) {
		$this->fifoHandle = fopen($file, 'a');
	}

	/**
	 * A test suite started.
	 *
	 * @param PHPUnit_Framework_TestSuite $suite
	 * @since  Method available since Release 2.2.0
	 */
	public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
		//$this->sendToDataCollector("[TEST] Starting test suite " . $suite->getName() . "\n");
		$this->sendToDataCollector(array(
			'event' => 'testsuite.start',
			'suiteClass' => get_class($suite),
			'suiteName' => $suite->getName(),
		));
	}

	/**
	 * A test suite ended.
	 *
	 * @param PHPUnit_Framework_TestSuite $suite
	 * @since  Method available since Release 2.2.0
	 */
	public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
		$this->sendToDataCollector(array(
			'event' => 'testsuite.end',
			'suiteClass' => get_class($suite),
			'suiteName' => $suite->getName(),
		));
	}

	/**
	 * A test started.
	 *
	 * @param PHPUnit_Framework_Test $test
	 */
	public function startTest(PHPUnit_Framework_Test $test) {
		if ($test instanceof \PHPUnit_Framework_TestCase) {
			$this->sendToDataCollector(array(
				'event' => 'test.start',
				'testClass' => get_class($test),
				'testName' => $test->getName(),
				'testNameWithoutDataSet' => $test->getName(FALSE),
			));
		}
	}

	/**
	 * A test ended.
	 *
	 * @param PHPUnit_Framework_Test $test
	 * @param float $time
	 */
	public function endTest(PHPUnit_Framework_Test $test, $time) {
		if ($test instanceof \PHPUnit_Framework_TestCase) {
			$this->sendToDataCollector(array(
				'event' => 'test.end',
				'testClass' => get_class($test),
				'testName' => $test->getName(),
				'testNameWithoutDataSet' => $test->getName(FALSE),
			));
		}
	}

	/**
	 * @param array $data
	 */
	protected function sendToDataCollector(array $data) {
		$encodedData = json_encode($data);
		$length = strlen($encodedData);

		fwrite($this->fifoHandle, $length . "\0" . $encodedData . "\0\n");
		echo "Wrote data: $length - $encodedData\n";
	}

}
 
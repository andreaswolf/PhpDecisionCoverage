<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit;


/**
 * Command used to let PHPUnit run in the forked process.
 *
 * This is necessary so we can add our listener to PHPUnit.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TestCommand extends \PHPUnit_TextUI_Command {

	/**
	 * The file path to the FIFO pipe.
	 *
	 * @var string
	 */
	protected $fifoFile;

	/**
	 * @var FifoMessageChannel
	 */
	protected $messageChannel;


	function __construct($fifoFile) {
		$this->fifoFile = $fifoFile;

		$fifoHandle = fopen($this->fifoFile, 'a');
		$this->messageChannel = new FifoMessageChannel($fifoHandle);
	}

	/**
	 * Adds the test listener for the connection to the data collector.
	 *
	 * @param array $argv
	 */
	protected function handleArguments(array $argv) {
		parent::handleArguments($argv);

		// the original command we extend here does not set the listeners, so we can safely set them here
		$this->arguments['listeners'] = array(new TestListener($this->messageChannel));
	}

	/**
	 * @param array $argv
	 * @param bool $exit
	 * @return int
	 */
	public function run(array $argv, $exit = TRUE) {
		$this->messageChannel->sendToDataCollector(array(
			'event' => 'testrun.start',
		));

		$result = parent::run($argv, FALSE);

		$this->messageChannel->sendToDataCollector(array(
			'event' => 'testrun.end',
		));

		if ($exit) {
			exit($result);
		} else {
			return $result;
		}
	}

}

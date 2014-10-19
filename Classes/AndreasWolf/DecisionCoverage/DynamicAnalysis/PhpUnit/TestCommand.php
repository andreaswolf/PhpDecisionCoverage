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


	function __construct($fifoFile) {
		$this->fifoFile = $fifoFile;
	}

	/**
	 * Adds the test listener for the connection to the data collector.
	 *
	 * @param array $argv
	 */
	protected function handleArguments(array $argv) {
		parent::handleArguments($argv);

		// the original command we extend here does not set the listeners, so we can safely set them here
		$this->arguments['listeners'] = array(new TestListener($this->fifoFile));
	}

}

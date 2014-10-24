<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit;

/**
 * A message channel (between processes) that uses a named pipe (FIFO queue).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class FifoMessageChannel {

	/**
	 * @var resource
	 */
	protected $fifoHandle;

	public function __construct($fifoHandle) {
		$this->fifoHandle = $fifoHandle;
	}

	/**
	 * @param array $data
	 */
	public function sendToDataCollector(array $data) {
		$encodedData = json_encode($data);
		$length = strlen($encodedData);

		fwrite($this->fifoHandle, $length . "\0" . $encodedData . "\0\n");
	}

}
 
<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit;

use AndreasWolf\DebuggerClient\Streams\StreamDataHandler;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;


/**
 * Wrapper for the PHPUnit test listener stream.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TestListenerOutputStream extends StreamWrapper implements StreamDataHandler {

	public function __construct($stream) {
		parent::__construct($stream);
		$this->dataHandler = $this;

		stream_set_timeout($stream, 1, 2000);
	}

	/**
	 * Handles data coming from the PHPUnit process.
	 */
	public function handleIncomingData() {
		$beginningOfNextPacket = '';
		$nextPacketLength = 0;
		$data = '';

		while ($readBytes = fgets($this->stream, 8192)) {
			$readBytes = trim($readBytes, "\n");

			$data .= $readBytes;
			do {
				list($datagram, $data) = explode("\0", $data, 2);

				echo "<<<<\n$datagram\n>>>>\n";
				if (is_numeric($datagram)) {
					$nextPacketLength = (int)$datagram;
				} else {
					if ($beginningOfNextPacket != '') {
						$datagram = $beginningOfNextPacket . $datagram;
						$beginningOfNextPacket = '';
					}
					if ($nextPacketLength >= strlen($datagram)) {
						echo "[DEBUG] Received data: " . $datagram . "\n";
					} else {
						// There’s more data to come…
						$beginningOfNextPacket = $datagram;
					}
				}
			} while (strpos($data, "\0") !== FALSE);
		}
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit;

use AndreasWolf\DebuggerClient\Streams\StreamDataHandler;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;
use AndreasWolf\DecisionCoverage\Core\Bootstrap;
use AndreasWolf\DecisionCoverage\Event\TestEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Wrapper for the PHPUnit test listener stream.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TestListenerOutputStream extends StreamWrapper implements StreamDataHandler {

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	public function __construct($stream, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}

		parent::__construct($stream);
		$this->dataHandler = $this;
		$this->eventDispatcher = $eventDispatcher;
		$this->logger = $logger;

		stream_set_blocking($stream, 0);
	}

	/**
	 * Handles data coming from the PHPUnit process.
	 */
	public function handleIncomingData() {
		$beginningOfNextPacket = $data = '';
		$nextPacketLength = 0;

		$packets = array();

		while ($readBytes = fgets($this->stream, 8192)) {
			$readBytes = trim($readBytes, "\n");

			$data .= $readBytes;
			do {
				list($datagram, $data) = explode("\0", $data, 2);

				if (is_numeric($datagram)) {
					$nextPacketLength = (int)$datagram;
				} else {
					if ($beginningOfNextPacket != '') {
						$datagram = $beginningOfNextPacket . $datagram;
						$beginningOfNextPacket = '';
					}
					if ($nextPacketLength >= strlen($datagram)) {
						$packets[] = $datagram;
					} else {
						// There’s more data to come…
						$beginningOfNextPacket = $datagram;
					}
				}
			} while (strpos($data, "\0") !== FALSE);
		}

		foreach ($packets as $packet) {
			$contents = json_decode($packet, TRUE);

			if ($contents === NULL) {
				continue;
			}

			if ($contents['event']) {
				$this->handleEventMessage($contents);
			}
		}
	}

	/**
	 * @param array $message
	 */
	protected function handleEventMessage($message) {
		switch ($message['event']) {
			case 'test.start':
			case 'test.end':
				$test = new Test($message['testClass'], $message['testName']);
				$event = new TestEvent($test);
				$this->logger->debug('Received event ' . $message['event'] . ' from test runner', $message);

				$this->eventDispatcher->dispatch($message['event'], $event);
		}
	}

}

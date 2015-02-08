<?php
namespace AndreasWolf\DecisionCoverage\Application;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;


class LogBuilder {

	/**
	 * @var HandlerInterface
	 */
	protected $handlers = array();


	/**
	 * @param string $type The handler type
	 * @param string $level The log level
	 * @param array $configuration Additional configuration for the handler
	 */
	public function addHandler($type, $level, $configuration) {
		switch ($type) {
			case 'file':
				$this->handlers[] = new StreamHandler($configuration['path'], $level);

				break;

			default:
				throw new \InvalidArgumentException('Invalid log handler type ' . $type);
		}
	}

	/**
	 * Adds a handler for outputting debug messages to the console.
	 */
	public function addConsoleDebugHandler() {
		$this->handlers[] = new StreamHandler(STDOUT, LogLevel::DEBUG);
	}

	/**
	 * @return LoggerInterface
	 */
	public function build() {
		// TODO make the name configurable
		$log = new Logger('DecisionCoverage', $this->handlers);
		return $log;
	}

}

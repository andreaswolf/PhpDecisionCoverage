<?php
namespace AndreasWolf\DecisionCoverage\Config;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;


class LogConfig {

	/**
	 * @var fDOMDocument
	 */
	protected $cfg;


	public function __construct($cfg) {
		$this->cfg = $cfg;
	}

	/**
	 * Reads and returns the log handlers from the configuration file.
	 *
	 * @return array
	 */
	public function getHandlers() {
		$handlers = $this->cfg->query('//handler[@type]');

		$logHandlers = [];
		/** @var fDOMElement $handler */
		foreach ($handlers as $handler) {
			$logHandler = array(
				'type' => (string)$handler->getAttribute('type'),
				'level' => (string)$handler->getAttribute('level'),
			);
			if ($handler->hasAttribute('path')) {
				$logHandler['path'] = (string)$handler->getAttribute('path');
			}

			$logHandlers[] = $logHandler;
		}

		return $logHandlers;
	}

}

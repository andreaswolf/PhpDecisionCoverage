<?php
namespace AndreasWolf\DecisionCoverage\Config;

use TheSeer\fDOM\fDOMDocument;


/**
 * Configuration object for the decision coverage application.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ApplicationConfig {

	/**
	 * @var fDOMDocument
	 */
	protected $root;


	public function __construct(fDOMDocument $root) {
		$this->root = $root;
	}

	/**
	 * @return LogConfig
	 */
	public function getLogConfig() {
		$logNode = $this->root->queryOne('//log');

		return new LogConfig($logNode);
	}

}

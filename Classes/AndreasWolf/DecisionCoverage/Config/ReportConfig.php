<?php
namespace AndreasWolf\DecisionCoverage\Config;

use TheSeer\fDOM\fDOMNode;


class ReportConfig {

	/**
	 * @var fDOMNode
	 */
	protected $cfg;


	/**
	 * @param fDOMNode $cfg The <project> configuration node
	 */
	public function __construct($cfg) {
		$this->cfg = $cfg;
	}

	public function getWriterType() {
		return (string)$this->cfg->queryOne('//writer')->getAttribute('format');
	}

	public function getOutputDirectory() {
		return (string)$this->cfg->queryOne('//writer')->getAttribute('outputdir');
	}

}

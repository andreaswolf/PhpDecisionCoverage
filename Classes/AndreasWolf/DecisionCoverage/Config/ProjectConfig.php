<?php
namespace AndreasWolf\DecisionCoverage\Config;

use TheSeer\fDOM\fDOMNode;


class ProjectConfig {

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

	public function getName() {
		return $this->cfg->attributes->getNamedItem('name')->textContent;
	}

	public function getSourceDirectory() {
		return new \SplFileInfo($this->cfg->attributes->getNamedItem('source')->textContent);
	}

	public function getWorkingDirectory() {
		return new \SplFileInfo($this->cfg->attributes->getNamedItem('workdir')->textContent);
	}

	public function getReportConfig() {
		$reportNode = $this->cfg->queryOne('//project/report');

		return new ReportConfig($reportNode);
	}

}

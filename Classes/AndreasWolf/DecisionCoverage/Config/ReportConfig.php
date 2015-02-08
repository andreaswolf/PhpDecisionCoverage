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
		return (string)$this->getWriter()->getAttribute('format');
	}

	/**
	 * @return \SplFileInfo
	 */
	public function getOutputDirectory() {
		$outputDir = (string)$this->getWriter()->getAttribute('outputdir');
		if (!$outputDir) {
			throw new \InvalidArgumentException('No output dir defined for report writer');
		}

		return new \SplFileInfo($outputDir);
	}

	/**
	 * @return mixed
	 */
	protected function getWriter() {
		$writer = $this->cfg->queryOne('//writer');
		if (!$writer) {
			throw new \InvalidArgumentException('No report writer defined');
		}

		return $writer;
	}

}

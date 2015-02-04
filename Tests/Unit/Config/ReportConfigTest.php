<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Config;

use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class ReportConfigTest extends UnitTestCase {
	use ConfigLoaderTrait;

	/**
	 * @test
	 */
	public function writerTypeCanBeRetrieved() {
		$reportConfig = $this->loadProjectConfiguration('SimpleConfiguration')->getReportConfig();

		$this->assertEquals('html', $reportConfig->getWriterType());
	}

	/**
	 * @test
	 */
	public function outputDirCanBeRetrieved() {
		$reportConfig = $this->loadProjectConfiguration('SimpleConfiguration')->getReportConfig();

		$this->assertEquals('/tmp/coverage/report/html/', $reportConfig->getOutputDirectory());
	}

}

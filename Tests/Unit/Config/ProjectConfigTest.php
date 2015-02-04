<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Config;

use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class ProjectConfigTest extends UnitTestCase {
	use ConfigLoaderTrait;

	/**
	 * @test
	 */
	public function projectNameCanBeRetrieved() {
		$applicationConfig = $this->loadConfiguration('SimpleConfiguration');
		$projectConfig = $applicationConfig->getProjectConfig();

		$this->assertEquals('someProject', $projectConfig->getName());
	}

	/**
	 * @test
	 */
	public function sourceAndWorkdirCanBeRetrieved() {
		$applicationConfig = $this->loadConfiguration('SimpleConfiguration');
		$projectConfig = $applicationConfig->getProjectConfig();

		$this->assertEquals('/tmp/coverage/projectSource', $projectConfig->getSourceDirectory());
		$this->assertEquals('/tmp/coverage/workDir', $projectConfig->getWorkingDirectory());
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Config;

use AndreasWolf\DecisionCoverage\Config\ConfigLoader;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class LogConfigTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function logConfigurationCanBeFetchedFromApplicationConfig() {
		$logConfig = $this->loadLogConfiguration('SimpleConfiguration');

		$handlers = $logConfig->getHandlers();

		$this->assertCount(1, $handlers);
	}

	/**
	 * @test
	 */
	public function fileLogHandlerIsResolvedCorrectly() {
		$logConfig = $this->loadLogConfiguration('SimpleConfiguration');

		$handlers = $logConfig->getHandlers();

		$this->assertEquals(array(
			'type' => 'file',
			'path' => 'file:///tmp/decisioncoverage.log',
			'level' => 'debug'
		), $handlers[0]);
	}

	/**
	 * @param string $fixture
	 * @return \AndreasWolf\DecisionCoverage\Config\LogConfig
	 * @throws \AndreasWolf\DecisionCoverage\Config\ConfigLoaderException
	 */
	protected function loadLogConfiguration($fixture) {
		$subject = new ConfigLoader();

		$appConfig = $subject->load(__DIR__ . '/../../Fixtures/Configuration/' . $fixture . '.xml');
		$logConfig = $appConfig->getLogConfig();

		return $logConfig;
	}

}

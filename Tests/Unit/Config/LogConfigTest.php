<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Config;

use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class LogConfigTest extends UnitTestCase {
	use ConfigLoaderTrait;

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

}

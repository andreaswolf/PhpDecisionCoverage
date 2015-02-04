<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Config;

use AndreasWolf\DecisionCoverage\Config\ApplicationConfig;
use AndreasWolf\DecisionCoverage\Config\ConfigLoader;
use AndreasWolf\DecisionCoverage\Config\ConfigLoaderException;
use AndreasWolf\DecisionCoverage\Config\LogConfig;


trait ConfigLoaderTrait {

	/**
	 * @param string $fixture
	 * @return LogConfig
	 * @throws ConfigLoaderException
	 */
	protected function loadLogConfiguration($fixture) {
		$appConfig = $this->loadConfiguration($fixture);
		$logConfig = $appConfig->getLogConfig();

		return $logConfig;
	}

	/**
	 * @param string $fixture
	 * @return ApplicationConfig
	 * @throws ConfigLoaderException
	 */
	protected function loadConfiguration($fixture) {
		$subject = new ConfigLoader();

		$appConfig = $subject->load(__DIR__ . '/../../Fixtures/Configuration/' . $fixture . '.xml');

		return $appConfig;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Config;

use AndreasWolf\DecisionCoverage\Config\ApplicationConfig;
use AndreasWolf\DecisionCoverage\Config\ConfigLoader;
use AndreasWolf\DecisionCoverage\Config\ConfigLoaderException;
use AndreasWolf\DecisionCoverage\Config\LogConfig;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class ConfigLoaderTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function loadThrowsExceptionIfFileIsNotFound() {
		$this->setExpectedException(ConfigLoaderException::class, '', ConfigLoaderException::NOT_FOUND);
		$subject = new ConfigLoader();

		$subject->load(__DIR__ . '/../../Fixtures/Configuration/AFileThatDoesNotExist.xml');
	}

	/**
	 * @test
	 */
	public function loadThrowsExceptionForWrongRootElementName() {
		$this->setExpectedException(ConfigLoaderException::class, '', ConfigLoaderException::INVALID_NAMESPACE);
		$subject = new ConfigLoader();

		$subject->load(__DIR__ . '/../../Fixtures/Configuration/InvalidRootElement.xml');
	}

	/**
	 * @test
	 */
	public function applicationConfigObjectIsReturnedForEmptyValidXmlFile() {
		$subject = new ConfigLoader();

		$configuration = $subject->load(__DIR__ . '/../../Fixtures/Configuration/EmptyRootElement.xml');

		$this->assertInstanceOf(ApplicationConfig::class, $configuration);
	}

	/**
	 * @test
	 */
	public function logConfigurationCanBeFetchedFromApplicationConfig() {
		$subject = new ConfigLoader();

		$configuration = $subject->load(__DIR__ . '/../../Fixtures/Configuration/SimpleConfiguration.xml');

		$this->assertInstanceOf(LogConfig::class, $configuration->getLogConfig());
	}

}

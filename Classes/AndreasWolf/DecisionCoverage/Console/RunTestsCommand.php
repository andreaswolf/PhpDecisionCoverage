<?php
namespace AndreasWolf\DecisionCoverage\Console;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DecisionCoverage\Config\ProjectConfig;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Persistence\SerializedObjectMapper as DynamicSerializedObjectMapper;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Persistence\SerializedObjectMapper as StaticSerializedObjectMapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Runs tests and collects analysis results.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class RunTestsCommand extends BaseCommand {

	/**
	 */
	protected function configure() {
		Bootstrap::getInstance()->run();

		$this->setName('run')
			->setDescription('Runs PHPUnit tests.');

		$this->addGenericOptions();
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return null|int
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$configuration = $this->loadConfiguration($input->getOption('config'));
		$projectConfig = $configuration->getProjectConfig();
		$this->logger = $this->initLog($configuration, $input->getOption('debug'));

		$analysisResult = $this->loadStaticAnalysisData($projectConfig);
		$dataSet = $this->performDynamicAnalysis($analysisResult, $projectConfig, $output);

		return NULL;
	}

	/**
	 * @param InputInterface $input
	 * @param string $optionName
	 */
	protected function assertOptionHasValue(InputInterface $input, $optionName) {
		if ($input->getOption($optionName) === NULL) {
			throw new \InvalidArgumentException('Option ' . $optionName . ' has to be set!');
		}
	}

	/**
	 * @param ProjectConfig $projectConfig
	 * @return \AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet
	 */
	protected function loadStaticAnalysisData(ProjectConfig $projectConfig) {
		$analysisDataMapper = new StaticSerializedObjectMapper();
		$analysisFile = $projectConfig->getWorkingDirectory() . '/static-analysis.bin';
		$staticAnalysisResults = $analysisDataMapper->loadFromFile($analysisFile);

		return $staticAnalysisResults;
	}

}

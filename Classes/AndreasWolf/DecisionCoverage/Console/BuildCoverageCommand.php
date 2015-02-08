<?php
namespace AndreasWolf\DecisionCoverage\Console;
use AndreasWolf\DecisionCoverage\Config\ProjectConfig;
use AndreasWolf\DecisionCoverage\Core\Bootstrap;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Persistence\SerializedObjectMapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * Command for building the coverage report from a dynamic analysis result.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BuildCoverageCommand extends BaseCommand {

	/**
	 */
	protected function configure() {
		Bootstrap::getInstance()->run();

		$this->setName('build')
			->setDescription('Calculates coverage.');

		$this->addGenericOptions();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$configuration = $this->loadConfiguration($input->getOption('config'));
		$projectConfig = $configuration->getProjectConfig();
		$this->logger = $this->initLog($configuration, $input->getOption('debug'));

		$eventDispatcher = new EventDispatcher();

		$dataSet = $this->loadCoverageData($projectConfig);
		$this->generateCoverageReport($dataSet, $projectConfig);
	}

	/**
	 * @param ProjectConfig $config
	 * @return CoverageDataSet
	 */
	protected function loadCoverageData(ProjectConfig $config) {
		$dataMapper = new SerializedObjectMapper();
		$coverageDataSet = $dataMapper->readFromFile($config->getWorkingDirectory() . '/dynamic-analysis.bin');

		return $coverageDataSet;
	}

}
 
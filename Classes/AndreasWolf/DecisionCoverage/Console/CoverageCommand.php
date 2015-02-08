<?php
namespace AndreasWolf\DecisionCoverage\Console;

use AndreasWolf\DebuggerClient\Core\Bootstrap as DebuggerBootstrap;
use AndreasWolf\DebuggerClient\Core\Client;
use AndreasWolf\DecisionCoverage\Config\ApplicationConfig;
use AndreasWolf\DecisionCoverage\Config\ProjectConfig;
use AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageCalculationDirector;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger\ClientEventSubscriber;
use AndreasWolf\DecisionCoverage\Report\Generator;
use AndreasWolf\DecisionCoverage\Report\Html\HtmlWriter;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileAnalyzer;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Persistence\SerializedObjectMapper;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * CLI command for building the coverage.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageCommand extends BaseCommand {

	protected function configure() {
		$this->setName('coverage')
			->setDescription('Determine the test coverage.');

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

		$eventDispatcher = new EventDispatcher();


		$output->writeln('<info>Starting static analysis</info>');
		$analysisResult = $this->performStaticAnalysis($eventDispatcher, $projectConfig);
		$output->writeln('<info>Static analysis finished</info>');

		$output->writeln('<info>Starting dynamic analysis</info>');
		$dataSet = $this->performDynamicAnalysis($analysisResult, $projectConfig, $output);
		$output->writeln('<info>Dynamic analysis finished</info>');

		$output->writeln('<info>Starting coverage reporting</info>');
		$this->generateCoverageReport($dataSet, $projectConfig, $output);
		$output->writeln('<info>Coverage reporting finished</info>');

		return NULL;
	}

}

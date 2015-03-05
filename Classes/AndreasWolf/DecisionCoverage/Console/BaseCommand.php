<?php
namespace AndreasWolf\DecisionCoverage\Console;

use AndreasWolf\DebuggerClient\Core\Bootstrap as DebuggerBootstrap;
use AndreasWolf\DebuggerClient\Core\Client;
use AndreasWolf\DecisionCoverage\Application\LogBuilder;
use AndreasWolf\DecisionCoverage\Config\ApplicationConfig;
use AndreasWolf\DecisionCoverage\Config\ConfigLoader;
use AndreasWolf\DecisionCoverage\Config\ProjectConfig;
use AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageCalculationDirector;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger\ClientEventSubscriber;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\TestProgressReporter;
use AndreasWolf\DecisionCoverage\Report\Generator;
use AndreasWolf\DecisionCoverage\Report\Html\HtmlWriter;
use AndreasWolf\DecisionCoverage\Report\Html\ReportFileXmlBuilder;
use AndreasWolf\DecisionCoverage\Report\ProjectXmlReportBuilder;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileAnalyzer;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Persistence\SerializedObjectMapper;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;


class BaseCommand extends Command {

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var OutputInterface
	 */
	protected $output;


	/**
	 * Adds options that all commands should support (output verbosity, configuration file, …)
	 */
	protected function addGenericOptions() {
		$this
			->addOption('debug', 'd', InputOption::VALUE_NONE, 'Flag to enable debug output to console.')
			->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'The configuration file to use', './coverage.xml');
	}

	protected function ensureConfiguredDirectoriesExist(ApplicationConfig $config) {
		$fs = new Filesystem();
		$dirs = array(
			$config->getProjectConfig()->getWorkingDirectory()->getPathname(),
			$config->getProjectConfig()->getSourceDirectory()->getPathname(),
			$config->getProjectConfig()->getReportConfig()->getOutputDirectory()->getPathname(),
		);
		$fs->mkdir($dirs);
	}

	/**
	 * @param string $configFile
	 * @return ApplicationConfig
	 * @throws \AndreasWolf\DecisionCoverage\Config\ConfigLoaderException
	 */
	protected function loadConfiguration($configFile) {
		$configLoader = new ConfigLoader();
		$configuration = $configLoader->load($configFile);

		$this->ensureConfiguredDirectoriesExist($configuration);

		return $configuration;
	}

	/**
	 * @param ApplicationConfig $config
	 * @param bool $debug
	 * @return LoggerInterface
	 */
	protected function initLog(ApplicationConfig $config, $debug) {
		$logConfiguration = $config->getLogConfig();
		$logBuilder = new LogBuilder();

		$handlers = $logConfiguration->getHandlers();
		foreach ($handlers as $handlerConfig) {
			$logBuilder->addHandler($handlerConfig['type'], $handlerConfig['level'], $handlerConfig);
		}
		if ($debug === TRUE) {
			$logBuilder->addConsoleDebugHandler();
		}

		return $logBuilder->build();
	}

	/**
	 * @param EventDispatcherInterface $eventDispatcher
	 * @param ProjectConfig $projectConfig
	 * @return ResultSet
	 */
	protected function performStaticAnalysis($eventDispatcher, $projectConfig) {
		$analyzer = new FileAnalyzer($eventDispatcher, $this->logger);
		$analysisResult = $analyzer->analyzeFolder($projectConfig->getSourceDirectory());
		$analysisResultMapper = new SerializedObjectMapper();
		$analysisResultMapper->saveToFile($projectConfig->getWorkingDirectory() . '/static-analysis.bin', $analysisResult);

		return $analysisResult;
	}

	/**
	 * @param ResultSet $analysisResult
	 * @param ProjectConfig $projectConfig
	 * @param OutputInterface $output
	 * @return CoverageDataSet
	 */
	protected function performDynamicAnalysis(ResultSet $analysisResult, ProjectConfig $projectConfig,
	                                          OutputInterface $output) {
		$debuggerEventDispatcher = new EventDispatcher();
		// TODO we should pass this to the client instance instead once the debugger client does not use the bootstrap
		// dispatcher anymore
		DebuggerBootstrap::getInstance()->injectEventDispatcher($debuggerEventDispatcher);

		$debuggerClient = new Client();
		// we only need one session, not continuous listening
		$debuggerClient->quitAfterCurrentSession();

		$dataSet = new CoverageDataSet($analysisResult);
		$this->createAndAttachEventSubscriber($projectConfig, $debuggerClient, $dataSet, $analysisResult, $output);

		$debuggerClient->run();

		$this->storeCoverageDataSet($projectConfig, $dataSet);

		return $dataSet;
	}

	/**
	 * @param ProjectConfig $projectConfig
	 * @param Client $debuggerClient
	 * @param CoverageDataSet $dataSet
	 * @param ResultSet $staticAnalysisResults
	 * @param OutputInterface $output
	 */
	protected function createAndAttachEventSubscriber(ProjectConfig $projectConfig, Client $debuggerClient,
	                                                  CoverageDataSet $dataSet, ResultSet $staticAnalysisResults,
	                                                  OutputInterface $output) {
		$clientEventSubscriber = new ClientEventSubscriber($debuggerClient, $dataSet, $output, $this->logger);
		$clientEventSubscriber->setStaticAnalysisResults($staticAnalysisResults);
		$clientEventSubscriber->setPhpUnitArguments($projectConfig->getPhpUnitArguments());

		$debuggerClient->addSubscriber($clientEventSubscriber);
	}

	/**
	 * @param ProjectConfig $projectConfig
	 * @param CoverageDataSet $dataSet
	 */
	protected function storeCoverageDataSet(ProjectConfig $projectConfig, CoverageDataSet $dataSet) {
		$dynamicDataMapper = new \AndreasWolf\DecisionCoverage\DynamicAnalysis\Persistence\SerializedObjectMapper();
		$dynamicDataMapper->writeToFile($projectConfig->getWorkingDirectory() . '/dynamic-analysis.bin', $dataSet);
	}

	/**
	 * @param CoverageDataSet $dataSet
	 * @param ProjectConfig $projectConfig
	 * @param OutputInterface $output
	 */
	protected function generateCoverageReport(CoverageDataSet $dataSet, ProjectConfig $projectConfig,
	                                          OutputInterface $output) {
		$coverageSet = new CoverageSet($dataSet);
		$director = new CoverageCalculationDirector($coverageSet, $output, NULL, NULL, NULL, $this->logger);
		$director->build($dataSet);

		$outputDir = $projectConfig->getReportConfig()->getOutputDirectory();
		$projectReportBuilder = new ProjectXmlReportBuilder($outputDir, $this->logger);
		$reportBuilders = array(
			$projectReportBuilder,
		);
		$writers = array(
			new HtmlWriter($outputDir, $this->logger),
		);

		$reportGenerator = new Generator($writers, $reportBuilders, $this->logger);
		$reportGenerator->generateCoverageReport($coverageSet);
	}

}

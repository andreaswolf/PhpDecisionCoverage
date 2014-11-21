<?php
namespace AndreasWolf\DecisionCoverage\Console;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Core\Client;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger\ClientEventSubscriber;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Persistence\SerializedObjectMapper;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Runs tests and collects analysis results.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class RunTestsCommand extends Command {

	/**
	 */
	protected function configure() {
		Bootstrap::getInstance()->run();

		$this->setName('run')
			->setDescription('Runs PHPUnit tests.')
			->addArgument('analysis-file', InputArgument::REQUIRED, 'The analysis file to use.')
			->addOption('phpunit-arguments', null, InputOption::VALUE_REQUIRED, 'Options for invoking PHPUnit.')
			->addOption('output', null, InputOption::VALUE_REQUIRED, 'The file to use for data gathered from the tests.');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return null|int
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->assertOptionHasValue($input, 'phpunit-arguments');
		$this->assertOptionHasValue($input, 'output');

		$debuggerClient = new Client();
		// we only need one session, not continuous listening
		$debuggerClient->quitAfterCurrentSession();

		$staticAnalysisResults = $this->loadStaticAnalysisData($input->getArgument('analysis-file'));
		$dataSet = new CoverageDataSet($staticAnalysisResults);
		$this->createAndAttachEventSubscriber($input, $debuggerClient, $dataSet, $staticAnalysisResults);

		$debuggerClient->run();

		file_put_contents($input->getOption('output'), serialize($dataSet));

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
	 * @param string $analysisFile
	 * @return \AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet
	 */
	protected function loadStaticAnalysisData($analysisFile) {
		$analysisDataMapper = new SerializedObjectMapper();
		$staticAnalysisResults = $analysisDataMapper->loadFromFile($analysisFile);

		return $staticAnalysisResults;
	}

	/**
	 * @param InputInterface $input
	 * @param Client $debuggerClient
	 * @param CoverageDataSet $dataSet
	 * @param ResultSet $staticAnalysisResults
	 */
	protected function createAndAttachEventSubscriber(InputInterface $input, Client $debuggerClient,
	                                                  CoverageDataSet $dataSet, ResultSet $staticAnalysisResults) {
		$clientEventSubscriber = new ClientEventSubscriber($debuggerClient, $dataSet);
		$clientEventSubscriber->setStaticAnalysisResults($staticAnalysisResults);
		$clientEventSubscriber->setPhpUnitArguments($input->getOption('phpunit-arguments'));

		$debuggerClient->addSubscriber($clientEventSubscriber);
	}

}

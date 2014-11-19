<?php
namespace AndreasWolf\DecisionCoverage\Console;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Core\Client;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger\ClientEventSubscriber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
			->addArgument('phpunit-arguments', InputArgument::REQUIRED, 'Options for invoking PHPUnit.');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return null|int
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$debuggerClient = new Client();
		$dataSet = new CoverageDataSet();
		$clientEventSubscriber = new ClientEventSubscriber($debuggerClient, $dataSet);
		$clientEventSubscriber->setStaticAnalysisFile($input->getArgument('analysis-file'));
		$clientEventSubscriber->setPhpUnitArguments($input->getArgument('phpunit-arguments'));
		$debuggerClient->addSubscriber($clientEventSubscriber);
		$debuggerClient->run();

		return NULL;
	}

}

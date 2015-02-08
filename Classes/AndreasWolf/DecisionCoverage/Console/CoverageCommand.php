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


		$analysisResult = $this->performStaticAnalysis($eventDispatcher, $projectConfig);

		// And this, kids, is the story of how PHP f*cked up my brain… Without this line, doing the next step (test running)
		// may fail with very weird symptoms–I had the debugger engine stream reach EOF before the first command
		// was issued by us. It took a few hours of continuous debugging to get to this point.
		// My best bet for now is that PHP’s garbage collection is a bit too aggressive here and removes parts of the
		// analysis object which hold a reference to the debugger engine stream. Another guess is a bug in the internal
		// resource reference system of PHP, but that’s really an uneducated and wild guess.
		//$analysisResult = unserialize(serialize($analysisResult));

		$dataSet = $this->performDynamicAnalysis($analysisResult, $projectConfig);

		$this->generateCoverageReport($dataSet, $projectConfig);

		return NULL;
	}

}

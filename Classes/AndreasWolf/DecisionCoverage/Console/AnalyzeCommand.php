<?php
namespace AndreasWolf\DecisionCoverage\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * Analyzes source code in a given directory and outputs the results to a file for later usage.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class AnalyzeCommand extends BaseCommand {

	/**
	 * @var InputInterface
	 */
	protected $input;

	/**
	 * @var string
	 */
	protected $basePath;

	/**
	 * @var string
	 */
	protected $project;


	protected function configure() {
		$this->setName('analyze')
			->setDescription('Analyzes source code and saves the result')
			->addArgument('base', InputArgument::OPTIONAL, 'The folder that should be analyzed.')
			->addOption('output', null, InputOption::VALUE_OPTIONAL, 'The file or directory to use for outputting the results. If a directory is given, the filename is automatically determined')
			->addOption('project', null, InputOption::VALUE_OPTIONAL, 'The project that is analyzed.', null);

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

		$this->performStaticAnalysis($eventDispatcher, $projectConfig);
	}

}

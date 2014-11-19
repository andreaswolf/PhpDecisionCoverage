<?php
namespace AndreasWolf\DecisionCoverage\Console;

use AndreasWolf\DecisionCoverage\StaticAnalysis\FileAnalyzer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Analyzes source code in a given directory and outputs the results to a file for later usage.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class AnalyzeCommand extends Command {

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
			->addArgument('base', InputArgument::REQUIRED, 'The folder that should be analyzed.')
			->addOption('output', null, InputOption::VALUE_OPTIONAL, 'The file or directory to use for outputting the results. If a directory is given, the filename is automatically determined')
			->addOption('project', null, InputOption::VALUE_OPTIONAL, 'The project that is analyzed.', null);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return null|int
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->basePath = realpath($input->getArgument('base'));
		$this->project = $input->getOption('project') ?: str_replace(DIRECTORY_SEPARATOR, '_', ltrim($this->basePath, '/'));
		$this->input = $input;

		$analyzer = new FileAnalyzer();
		$analysisResult = $analyzer->analyzeFolder($this->basePath);

		$this->getOutputFilePath();
		$analyzer->writeAnalysisResultsToFile($this->getOutputFilePath(), $analysisResult);
	}

	/**
	 * Generates the file path for the output file, either for a user-defined or a default path.
	 *
	 * @return string
	 */
	protected function getOutputFilePath() {
		$outputPath = $this->input->getOption('output');
		if (!$outputPath) {
			// default output dir
			$outputPath = realpath(__DIR__ . '/../../../../Output');
		}
		if (is_dir($outputPath)) {
			$outputPath = rtrim($outputPath, '/') . '/';

			if (is_dir($outputPath)) {
				$outputPath .= sprintf('%s_%s.out', date('Y-m-d_H-i'), $this->project);
			}
		}

		return $outputPath;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Console;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DecisionCoverage\Source\SyntaxTreePrinter;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Persistence\SerializedObjectMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class DumpStaticAnalysisCommand extends Command {

	protected function configure() {
		Bootstrap::getInstance()->run();

		$this->setName('dump-static-result')
			->setDescription('Prints the syntax trees resulting from a static analysis.')
			->addArgument('analysis-file', InputArgument::REQUIRED, 'The analysis file to use.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$analysisData = $this->loadStaticAnalysisData($input->getArgument('analysis-file'));

		$treePrinter = new SyntaxTreePrinter();
		foreach ($analysisData->getFileResults() as $filePath => $fileAnalysisResult) {
			echo "\n";
			echo "Analyzed file ", $filePath, "\n";

			$treePrinter->printTree($fileAnalysisResult->getSyntaxTree(), TRUE);
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

}
 
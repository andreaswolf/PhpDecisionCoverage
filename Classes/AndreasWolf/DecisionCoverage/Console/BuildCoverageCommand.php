<?php
namespace AndreasWolf\DecisionCoverage\Console;
use AndreasWolf\DecisionCoverage\Core\Bootstrap;
use AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageCalculationDirector;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Persistence\SerializedObjectMapper;
use AndreasWolf\DecisionCoverage\Report\Generator;
use AndreasWolf\DecisionCoverage\Report\Html\HtmlWriter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Command for building the coverage report from a dynamic analysis result.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BuildCoverageCommand extends Command {

	/**
	 */
	protected function configure() {
		Bootstrap::getInstance()->run();

		$this->setName('build')
			->setDescription('Calculates coverage.')
			->addArgument('coverage-file', InputArgument::REQUIRED, 'The analysis file to use.')
			->addOption('output', null, InputOption::VALUE_REQUIRED, 'The file to use for coverage data gathered from the tests.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$coverageDataSet = $this->loadCoverageData($input->getArgument('coverage-file'));

		$log = new Logger('BuildCoverage');
		$log->pushHandler(new StreamHandler('/tmp/debug.log'));
		if ($input->getOption('verbose') === TRUE) {
			$log->pushHandler(new StreamHandler(STDOUT));
		}

		$coverageSet = new CoverageSet($coverageDataSet);
		$director = new CoverageCalculationDirector($coverageSet, NULL, NULL, NULL, $log);
		$director->build($coverageDataSet);

		$tempDir = $this->makeTemporaryDirectory();
		$writers = array(new HtmlWriter($tempDir, $log));

		$reportGenerator = new Generator($writers, $log);
		$reportGenerator->generateCoverageReport($coverageSet);

		$outputFile = $input->getOption('output');
		if (!$outputFile) {
			$outputFile = tempnam(sys_get_temp_dir(), 'coverage-output-');
			$log->warn('No log file defined. Outputting generated coverage to ' . $outputFile);
		}
		file_put_contents($outputFile, serialize($coverageSet));
	}

	/**
	 * @param string $fileName
	 * @return CoverageDataSet
	 */
	protected function loadCoverageData($fileName) {
		$dataMapper = new SerializedObjectMapper();
		$coverageDataSet = $dataMapper->readFromFile($fileName);

		return $coverageDataSet;
	}

	/**
	 * @return string
	 */
	protected function makeTemporaryDirectory() {
		// did not test this for real randomness, but should be enough for this purpose
		$randomHash = sha1((string)microtime() . (string)mt_rand(0, 10000));
		$directory = sys_get_temp_dir() . '/coverage-' . substr($randomHash, 0, 10);
		mkdir($directory);

		return $directory;
}

}
 
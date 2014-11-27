<?php
namespace AndreasWolf\DecisionCoverage\Console;
use AndreasWolf\DecisionCoverage\Core\Bootstrap;
use AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageCalculationDirector;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Persistence\SerializedObjectMapper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 *
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

		$builder = new CoverageCalculationDirector();
		$coverageData = $builder->buildFromDataSet($coverageDataSet);

		echo var_export($coverageData);
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


}
 
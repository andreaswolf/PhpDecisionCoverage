<?php
namespace AndreasWolf\DecisionCoverage\Console;
use AndreasWolf\DecisionCoverage\Core\Bootstrap;
use AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageBuilder;
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
		$coverageDataSet = unserialize(file_get_contents($input->getArgument('coverage-file')));

		$factory = new CoverageBuilder();
		$coverageData = $factory->buildFromDataSet($coverageDataSet);

		print_r($coverageData);
	}


}
 
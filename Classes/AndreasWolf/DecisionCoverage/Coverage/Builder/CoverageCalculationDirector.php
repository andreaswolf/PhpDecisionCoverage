<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\CoverageBuildProgressReporter;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageDataSetEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\FileCoverageEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\SampleEvent;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Event\SyntaxTreeIteratorEvent;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use AndreasWolf\DecisionCoverage\Source\RecursiveSyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\SyntaxTreeStack;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageCalculationDirector {

	/**
	 * @var CoverageSet
	 */
	protected $coverageSet;

	/**
	 * @var CoverageBuilderFactory
	 */
	protected $builderFactory;

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;

	/**
	 * @var LoggerInterface
	 */
	protected $log;


	/**
	 * @param CoverageSet $coverageSet The coverage data set to insert the generated coverage into
	 * @param OutputInterface $output
	 * @param CoverageBuilderFactory $factory
	 * @param ExpressionService $expressionService
	 * @param EventDispatcherInterface $eventDispatcher The event dispatcher to use. Optional because the events used
	 *   here don't necessarily need to be handled globally
	 * @param LoggerInterface $log
	 */
	public function __construct(CoverageSet $coverageSet, OutputInterface $output,
	                            CoverageBuilderFactory $factory = NULL, ExpressionService $expressionService = NULL,
	                            EventDispatcherInterface $eventDispatcher = NULL, LoggerInterface $log = NULL) {
		if (!$eventDispatcher) {
			$eventDispatcher = new EventDispatcher();
		}
		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}
		if (!$log) {
			$log = new NullLogger();
		}
		if (!$factory) {
			$factory = new CoverageBuilderFactory($eventDispatcher,
				new CoverageFactory($expressionService, new DecisionInputBuilder($log)), $log);
		}

		$this->coverageSet = $coverageSet;
		$this->builderFactory = $factory;
		$this->eventDispatcher = $eventDispatcher;
		$this->expressionService = $expressionService;
		$this->log = $log;

		if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
			$this->eventDispatcher->addSubscriber(new CoverageBuildProgressReporter($output));
		}
	}

	/**
	 */
	public function build() {
		$dataSet = $this->coverageSet->getDataSet();
		$this->createCoverageBuildersForDataSet($dataSet);
		$this->eventDispatcher->dispatch('coverage.build.start', new CoverageDataSetEvent($dataSet));

		$samples = $dataSet->getSamples();
		foreach ($samples as $dataSample) {
			$this->eventDispatcher->dispatch('coverage.sample.received', new SampleEvent($dataSample));
		}

		$this->eventDispatcher->dispatch('coverage.build.end', new CoverageDataSetEvent($dataSet));
	}

	/**
	 */
	protected function createCoverageBuildersForDataSet() {
		$treeStack = new SyntaxTreeStack($this->eventDispatcher);
		$this->eventDispatcher->addSubscriber($treeStack);
		$aggregateBuilder = new CoverageAggregateBuilder($this->eventDispatcher, $this->builderFactory, $this->log);
		$this->eventDispatcher->addSubscriber($aggregateBuilder);

		foreach ($this->coverageSet->getAnalysisResult()->getFileResults() as $potentiallyCoveredFile) {
			$this->createFileCoverageBuilders($potentiallyCoveredFile);
		}

		$this->eventDispatcher->removeSubscriber($treeStack);
	}

	/**
	 * @param FileResult $file
	 */
	protected function createFileCoverageBuilders(FileResult $file) {
		$this->log->debug('Starting to create builders for file ' . $file->getFilePath());

		$fileCoverage = new FileCoverage($file->getFilePath());
		$this->coverageSet->add($fileCoverage);

		$this->eventDispatcher->dispatch('syntaxtree.file.entered', new FileCoverageEvent($fileCoverage));

		$fileIterator = $this->createFileIterator($file);
		foreach ($fileIterator as $_) {
			$this->eventDispatcher->dispatch('syntaxtree.node',
				new SyntaxTreeIteratorEvent($fileIterator->getInnerIterator())
			);
		}

		$this->eventDispatcher->dispatch('syntaxtree.file.left', new FileCoverageEvent($fileCoverage));
	}

	/**
	 * @param FileResult $file
	 * @return RecursiveSyntaxTreeIterator
	 */
	protected function createFileIterator(FileResult $file) {
		return new RecursiveSyntaxTreeIterator(new SyntaxTreeIterator($file->getSyntaxTree()->getRootNodes(), FALSE),
			$this->eventDispatcher, \RecursiveIteratorIterator::SELF_FIRST
		);
	}

}
 
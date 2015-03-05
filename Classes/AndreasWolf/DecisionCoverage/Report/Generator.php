<?php
namespace AndreasWolf\DecisionCoverage\Report;

use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Report\Html\SourceFile;
use AndreasWolf\DecisionCoverage\Report\Html\SourceFileTokenizer;
use PhpParser\Node;
use PhpParser\Node\Expr;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class Generator {

	/**
	 * @var Writer[]
	 */
	protected $writers;

	/**
	 * @var ReportBuilder[]
	 */
	protected $reportBuilders;

	/**
	 * @var LoggerInterface
	 */
	protected $log;


	/**
	 * @param Writer[] $writers
	 * @param ReportBuilder[] $reporterBuilders
	 * @param LoggerInterface $log
	 */
	public function __construct($writers, $reporterBuilders, LoggerInterface $log = NULL) {
		$this->writers = $writers;
		$this->reportBuilders = $reporterBuilders;

		$this->log = $log !== NULL ? $log : new NullLogger();
	}

	/**
	 * Creates a report for all coverages within the given set.
	 *
	 * The report generation is actually a three-step process:
	 *  1. the source file is read and split in lines
	 *  2. the source lines are annotated if they contain coverage information
	 *  3. the report is generated from the annotated lines
	 *
	 * @param CoverageSet $coverageSet
	 */
	public function generateCoverageReport(CoverageSet $coverageSet) {
		$this->log->debug('Started generating coverage report');

		$enricher = new SourceFileAnnotationEnricher();

		foreach ($coverageSet->getAll() as $fileCoverage) {
			$this->log->debug('Generating coverage report for ' . $fileCoverage->getFilePath());
			$sourceFile = $this->generateSourceFile($fileCoverage);

			$enricher->attachCoverageAnnotationsToSourceFile($fileCoverage, $sourceFile);

			// TODO unify these interfaces if possible, or replace them with something more clever
			foreach ($this->reportBuilders as $builder) {
				$builder->handleFileCoverage($fileCoverage);
			}
			foreach ($this->writers as $writer) {
				$writer->writeReportForSourceFile($sourceFile);
			}
		}
	}

	/**
	 * Returns a SourceFile object which contains all lines of a file with their relative character offsets
	 * within the file.
	 *
	 * @param FileCoverage $coverage
	 * @return SourceFile
	 */
	protected function generateSourceFile(FileCoverage $coverage) {
		$filePath = $coverage->getFilePath();

		$tokenizer = new SourceFileTokenizer();
		$tokenizedFile = $tokenizer->getSourceLinesInFile($filePath);

		$sourceFile = SourceFile::createFromTokenizationResult($tokenizedFile);

		return $sourceFile;
	}

}

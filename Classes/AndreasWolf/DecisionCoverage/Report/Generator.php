<?php
namespace AndreasWolf\DecisionCoverage\Report;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\CoverageAggregate;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Report\Annotation\DecisionCoverageAnnotation;
use AndreasWolf\DecisionCoverage\Report\Html\SourceFile;
use AndreasWolf\DecisionCoverage\Report\Html\SourceFileTokenizer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class Generator {

	/**
	 * @var Writer[]
	 */
	protected $writers;

	/**
	 * @var LoggerInterface
	 */
	protected $log;


	/**
	 * @param Writer[] $writers
	 */
	public function __construct($writers = array(), LoggerInterface $log = NULL) {
		$this->writers = $writers;
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
		foreach ($coverageSet->getAll() as $fileCoverage) {
			$this->log->debug('Generating coverage report for ' . $fileCoverage->getFilePath());
			$sourceFile = $this->generateSourceFile($fileCoverage);

			$this->attachCoverageAnnotationsToSourceFile($fileCoverage, $sourceFile);

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

	/**
	 * Attaches coverage annotations to source lines where appropriate.
	 *
	 * These annotations are later on transformed into XML fragment nodes and will be rendered specially in the final
	 * report.
	 *
	 * @param Coverage|CoverageAggregate $coverage
	 * @param SourceFile $sourceFile
	 */
	protected function attachCoverageAnnotationsToSourceFile($coverage, SourceFile $sourceFile) {
		if ($coverage instanceof MethodCoverage) {
			$sourceFile->addCoverage($coverage->getId(), $coverage);
		}

		if ($coverage instanceof CoverageAggregate) {
			foreach ($coverage->getCoverages() as $subcoverage) {
				$this->attachCoverageAnnotationsToSourceFile($subcoverage, $sourceFile);
			}
		}

		if ($coverage instanceof DecisionCoverage) {
			$sourceFile->addCoverage($coverage->getId(), $coverage);

			$expression = $coverage->getExpression();
			$startPosition = $expression->getAttribute('startFilePos');
			$endPosition = $expression->getAttribute('endFilePos');
			$annotation = new DecisionCoverageAnnotation($coverage);

			$line = $sourceFile->getLineByCharacterOffset($startPosition);
			$line->annotate($startPosition, $endPosition, $annotation);
		}
	}

}

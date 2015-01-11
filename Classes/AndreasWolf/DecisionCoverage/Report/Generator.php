<?php
namespace AndreasWolf\DecisionCoverage\Report;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\CoverageAggregate;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
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

	public function generateCoverageReport(CoverageSet $coverageSet) {
		foreach ($coverageSet->getAll() as $fileCoverage) {
			$this->log->debug('Generating coverage report for ' . $fileCoverage->getFilePath());
			$sourceFile = $this->generateSourceFile($fileCoverage);

			$this->attachCoverageAnnotationsToSourceFile($fileCoverage, $sourceFile);

			foreach ($this->writers as $writer) {
				$writer->writeReportForSourceFile($sourceFile);
			}
		}
	}

	protected function generateSourceFile(FileCoverage $coverage) {
		$filePath = $coverage->getFilePath();

		$tokenizer = new SourceFileTokenizer();
		$tokenizedFile = $tokenizer->getSourceLinesInFile($filePath);

		$sourceFile = SourceFile::createFromTokenizationResult($tokenizedFile);

		return $sourceFile;
	}


	protected function attachCoverageAnnotationsToSourceFile($coverage, SourceFile $sourceFile) {
		if ($coverage instanceof CoverageAggregate) {
			foreach ($coverage->getCoverages() as $subcoverage) {
				$this->attachCoverageAnnotationsToSourceFile($subcoverage, $sourceFile);
			}
		} elseif ($coverage instanceof DecisionCoverage) {
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

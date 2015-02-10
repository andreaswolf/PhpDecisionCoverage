<?php
namespace AndreasWolf\DecisionCoverage\Report;

use AndreasWolf\DecisionCoverage\Coverage\ClassCoverage;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\InputCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Report\Annotation\DecisionCoverageAnnotation;
use AndreasWolf\DecisionCoverage\Report\Annotation\MethodCoverageAnnotation;
use AndreasWolf\DecisionCoverage\Report\Html\SourceFile;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


/**
 * Adds annotations for coverage objects to a source file.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SourceFileAnnotationEnricher {

	/**
	 * @var LoggerInterface
	 */
	protected $log;


	/**
	 * @param LoggerInterface $log
	 */
	public function __construct(LoggerInterface $log = NULL) {
		$this->log = $log !== NULL ? $log : new NullLogger();
	}

	/**
	 * Attaches coverage annotations to source lines where appropriate.
	 *
	 * These annotations are later on transformed into XML fragment nodes and will be rendered specially in the final
	 * report.
	 *
	 * @param FileCoverage $coverage
	 * @param SourceFile $sourceFile
	 */
	public function attachCoverageAnnotationsToSourceFile(FileCoverage $coverage, SourceFile $sourceFile) {
			$this->log->debug('Attaching file coverage for ' . $coverage->getFilePath());
			foreach ($coverage->getCoverages() as $subCoverage) {
				$this->attachClassCoverageAnnotation($subCoverage, $sourceFile);
			}
		}

		/**
		 * @param ClassCoverage $coverage
		 * @param SourceFile $sourceFile
		 */
		protected function attachClassCoverageAnnotation(ClassCoverage $coverage, SourceFile $sourceFile) {
			$this->log->debug('Attaching class coverage for ' . $coverage->getClassName());

			// TODO add class coverage annotation

			foreach ($coverage->getMethodCoverages() as $subCoverage) {
				$this->attachMethodCoverageAnnotation($subCoverage, $sourceFile);
			}
		}

		/**
		 * @param MethodCoverage $coverage
		 * @param SourceFile $sourceFile
		 */
		protected function attachMethodCoverageAnnotation(MethodCoverage $coverage, SourceFile $sourceFile) {
			$this->log->debug('Attaching method coverage for ' . $coverage->getMethodName());

			$sourceFile->addCoverage($coverage->getId(), $coverage);
			$annotation = new MethodCoverageAnnotation($coverage);
			$this->addAnnotation($sourceFile, $annotation, $coverage->getNode());

			foreach ($coverage->getDecisionCoverages() as $subCoverage) {
				$this->attachInputCoverageAnnotation($subCoverage, $sourceFile);
			}
		}

		/**
		 * @param InputCoverage $coverage
		 * @param SourceFile $sourceFile
		 */
		protected function attachInputCoverageAnnotation(InputCoverage $coverage, SourceFile $sourceFile) {
			if (!$coverage instanceof DecisionCoverage) {
				// TODO implement support for SingleConditionCoverage
				return;
			}
			$this->log->debug('Attaching decision coverage for ' . $coverage->getId());

			$sourceFile->addCoverage($coverage->getId(), $coverage);

			$annotation = new DecisionCoverageAnnotation($coverage);
			$this->addAnnotation($sourceFile, $annotation, $coverage->getExpression());
		}

		/**
		 * @param SourceFile $sourceFile
		 * @param $annotation
		 * @param Node $expression
		 */
		protected function addAnnotation(SourceFile $sourceFile, $annotation, Node $expression) {
			$startPosition = $expression->getAttribute('startFilePos');
			$endPosition = $expression->getAttribute('endFilePos');
			$line = $sourceFile->getLineByCharacterOffset($startPosition);
			$line->annotate($startPosition, $endPosition, $annotation);
		}

}

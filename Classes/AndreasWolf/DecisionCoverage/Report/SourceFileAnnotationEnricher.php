<?php
namespace AndreasWolf\DecisionCoverage\Report;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\CoverageAggregate;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Report\Annotation\DecisionCoverageAnnotation;
use AndreasWolf\DecisionCoverage\Report\Html\SourceFile;


/**
 * Adds annotations for coverage objects to a source file.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SourceFileAnnotationEnricher {

	/**
	 * Attaches coverage annotations to source lines where appropriate.
	 *
	 * These annotations are later on transformed into XML fragment nodes and will be rendered specially in the final
	 * report.
	 *
	 * @param Coverage|CoverageAggregate $coverage
	 * @param SourceFile $sourceFile
	 */
	public function attachCoverageAnnotationsToSourceFile($coverage, SourceFile $sourceFile) {
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

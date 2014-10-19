<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;

use AndreasWolf\DecisionCoverage\Source\SourceFile;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Instrumenter;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\BreakpointFactory;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\NodeIdGenerator;


class FileAnalyzer {

	/**
	 * @param SourceFile $file
	 * @return FileResult
	 */
	public function analyzeFile(SourceFile $file) {
		$nodes = $file->getTopLevelStatements();
		$result = new FileResult($file->getFilePath(), $nodes);

		$instrumenter = new Instrumenter();
		$instrumenter->addVisitor(new NodeIdGenerator(), 0);
		$instrumenter->addVisitor(new BreakpointFactory($result), 1);
		$instrumenter->instrument($nodes);

		return $result;
	}

	/**
	 * Writes the results to the given file.
	 *
	 * @param string $file
	 * @param ResultSet $result
	 */
	public function writeAnalysisResultsToFile($file, ResultSet $result) {
		$result = file_put_contents($file, $result->serialize());

		if ($result === FALSE) {
			throw new \RuntimeException('Could not write results to file', 1413653932);
		}
	}

}

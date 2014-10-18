<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;

use AndreasWolf\DecisionCoverage\Source\SourceFile;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Instrumenter;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\BreakpointFactory;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\NodeIdGenerator;


class FileAnalyzer {

	public function analyzeFile(SourceFile $file) {
		$nodes = $file->getTopLevelStatements();
		$result = new FileResult($file->getFilePath(), $nodes);

		$instrumenter = new Instrumenter();
		$instrumenter->addVisitor(new NodeIdGenerator(), 0);
		$instrumenter->addVisitor(new BreakpointFactory($result), 1);
		$instrumenter->instrument($nodes);

		return $result;
	}

}

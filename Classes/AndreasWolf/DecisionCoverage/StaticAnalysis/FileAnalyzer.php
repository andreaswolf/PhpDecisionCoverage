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

	public function analyzeFolder($folder) {
		if (!file_exists($folder) || !is_dir($folder)) {
			throw new \InvalidArgumentException($folder . ' does not exist or is no folder.', 1413747411);
		}

		$directoryIterator = new \RecursiveDirectoryIterator(realpath($folder));
		$fileIterator = new \RecursiveIteratorIterator(new \RecursiveCallbackFilterIterator($directoryIterator,
			function ($current, $key, $iterator) {
				/** @var $iterator \RecursiveIterator */
				/** @var $current \DirectoryIterator */
				// Allow recursion
				if ($iterator->hasChildren()) {
					return TRUE;
				}
				// Check for large file
				if ($current->isFile() && substr($current->getFilename(), -4) == '.php') {
					return TRUE;
				}

				return FALSE;
			}
		));

		$parser = new \PhpParser\Parser(new \PhpParser\Lexer());
		$resultSet = new ResultSet();
		foreach ($fileIterator as $file) {
			$sourceFile = new SourceFile($file->getPathname());
			$sourceFile->setParser($parser);
			$fileResult = $this->analyzeFile($sourceFile);

			$resultSet->addFileResult($fileResult);
		}

		return $resultSet;
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

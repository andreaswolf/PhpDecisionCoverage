<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


class SourceFileTokenizer {

	/**
	 * @param $filePath
	 * @return TokenizationResult
	 */
	public function getSourceLinesInFile($filePath) {
		$fileHandle = fopen($filePath, 'r');

		$lines = [];
		while ($line = fgets($fileHandle)) {
			$lines[] = rtrim($line, "\r\n");
		}

		return new TokenizationResult($lines);
	}

}

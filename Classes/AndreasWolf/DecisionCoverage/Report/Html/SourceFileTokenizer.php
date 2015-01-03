<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


class SourceFileTokenizer {

	/**
	 * @param $filePath
	 * @return TokenizationResult
	 */
	public function getSourceLinesInFile($filePath) {
		$fileHandle = fopen($filePath, 'r');

		$offset = 0;
		$lines = $offsets = [];
		while ($line = fgets($fileHandle)) {
			$offsets[] = $offset;
			$lines[] = rtrim($line, "\r\n");
			$offset += strlen($line);
		}

		return new TokenizationResult($lines, $offsets);
	}

}

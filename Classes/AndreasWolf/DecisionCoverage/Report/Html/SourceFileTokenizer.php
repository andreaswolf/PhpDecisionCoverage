<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


/**
 * (Line-based) tokenizer for PHP code files.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SourceFileTokenizer {

	/**
	 * Splits a file by line endings and calculates the relative offset of each line.
	 *
	 * @param string $filePath
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

		return new TokenizationResult($filePath, $lines, $offsets);
	}

}

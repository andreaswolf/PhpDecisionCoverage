<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


class SourceFileTokenizer {

	/**
	 * @param $filePath
	 * @return array An array of the lines, with the offset for each line
	 */
	public function getSourceLinesInFile($filePath) {
		$fileHandle = fopen($filePath, 'r');

		$lines = [];
		while ($line = fgets($fileHandle)) {
			$lines[] = rtrim($line, "\r\n");
		}

		return $lines;
	}

}

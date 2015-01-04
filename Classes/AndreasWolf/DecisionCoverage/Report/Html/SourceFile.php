<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


class SourceFile {

	/**
	 * @var SourceLine[]
	 */
	protected $lines;


	/**
	 * @param SourceLine[] $lines
	 */
	public function __construct($lines) {
		$this->lines = $lines;
	}

	public static function createFromTokenizationResult(TokenizationResult $result) {
		$lineCount = $result->countLines();
		$lines = array();
		for ($i = 1; $i <= $lineCount; ++$i) {
			$lines[] = SourceLine::createFromTokenizationResult($result, $i);
		}

		return new self($lines);
	}

	/**
	 * @return SourceLine[]
	 */
	public function getLines() {
		return $this->lines;
	}

	/**
	 * @param int $number
	 * @return SourceLine
	 */
	public function getLine($number) {
		return $this->lines[$number - 1];
	}

	/**
	 * @param int $offset
	 * @return SourceLine
	 */
	public function getLineByCharacterOffset($offset) {
		$lastLine = NULL;
		foreach ($this->lines as $line) {
			if ($line->getOffset() == $offset || $line->getOffset() + strlen($line->getContents()) > $offset) {
				return $line;
			}
			$lastLine = $line;
		}
	}

}

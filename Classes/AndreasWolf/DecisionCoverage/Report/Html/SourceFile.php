<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


use AndreasWolf\DecisionCoverage\Coverage\Coverage;


class SourceFile {

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var SourceLine[]
	 */
	protected $lines = [];

	/**
	 * @var Coverage[]
	 */
	protected $coverages = [];


	/**
	 * @param string $path
	 * @param SourceLine[] $lines
	 */
	public function __construct($path, $lines) {
		$this->path = $path;
		$this->lines = $lines;
	}

	public static function createFromTokenizationResult(TokenizationResult $result) {
		$lineCount = $result->countLines();
		$lines = array();
		for ($i = 1; $i <= $lineCount; ++$i) {
			$lines[] = SourceLine::createFromTokenizationResult($result, $i);
		}

		return new self($result->getFilePath(), $lines);
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
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

	/**
	 * Adds a coverage for this source file
	 *
	 * @param string $id
	 * @param Coverage $coverage
	 */
	public function addCoverage($id, $coverage) {
		$this->coverages[$id] = $coverage;
	}

	/**
	 * @return \AndreasWolf\DecisionCoverage\Coverage\Coverage[]
	 */
	public function getCoverages() {
		return $this->coverages;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


class TokenizationResult {

	/**
	 * @var string
	 */
	protected $filePath;

	/**
	 * @var string[]
	 */
	protected $lines;

	/**
	 * @var int[]
	 */
	protected $offsets;


	public function __construct($filePath, $lines, $lineOffsets) {
		$this->filePath = $filePath;
		$this->lines = $lines;
		$this->offsets = $lineOffsets;
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}

	public function countLines() {
		return count($this->lines);
	}

	public function getLine($number) {
		return $this->lines[$number - 1];
	}

	public function getLineOffset($number) {
		return $this->offsets[$number - 1];
	}

}

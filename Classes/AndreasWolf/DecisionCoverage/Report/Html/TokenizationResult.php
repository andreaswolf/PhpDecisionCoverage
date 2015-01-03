<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


class TokenizationResult {

	/**
	 * @var string[]
	 */
	protected $lines;

	/**
	 * @var int[]
	 */
	protected $offsets;


	public function __construct($lines, $lineOffsets) {
		$this->lines = $lines;
		$this->offsets = $lineOffsets;
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

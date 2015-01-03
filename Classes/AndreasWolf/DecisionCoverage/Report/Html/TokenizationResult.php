<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


class TokenizationResult {

	/**
	 * @var string[]
	 */
	protected $lines;


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

}

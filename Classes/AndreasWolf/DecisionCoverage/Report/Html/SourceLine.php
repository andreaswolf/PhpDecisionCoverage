<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


class SourceLine {

	/**
	 * @var string
	 */
	protected $contents;

	/**
	 * @var int
	 */
	protected $offset;


	/**
	 * @param string $contents
	 * @param int $offset
	 */
	public function __construct($contents, $offset) {
		$this->contents = $contents;
		$this->offset = $offset;
	}

	public static function createFromTokenizationResult(TokenizationResult $result, $lineNumber) {
		return new static($result->getLine($lineNumber), $result->getLineOffset($lineNumber));
	}

	/**
	 * @return string
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * @return int
	 */
	public function getOffset() {
		return $this->offset;
	}

}

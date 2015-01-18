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
	 * @var array FIXME
	 */
	protected $annotations = array();


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

	/**
	 * @param int $startOffset
	 * @param int $endOffset
	 * @param $annotation TODO introduce an annotation interface
	 */
	public function annotate($startOffset, $endOffset, $annotation) {
		// TODO check if offsets are within the line

		$this->annotations[] = array(
			'start' => $startOffset,
			'end' => $endOffset,
			'annotation' => $annotation,
		);
	}

	/**
	 * @return array
	 */
	public function getAnnotations() {
		return $this->annotations;
	}

	/**
	 * @return bool
	 */
	public function hasAnnotations() {
		return count($this->annotations) > 0;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;
use PhpParser\Node;


/**
 * The result of the static analysis of a file.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class FileResult {

	/**
	 * @var string
	 */
	protected $filePath;

	/**
	 * @var Node[]
	 */
	protected $syntaxTree;

	/**
	 * @var Breakpoint[]
	 */
	protected $breakpoints = array();


	public function __construct($filePath, $syntaxTree) {
		$this->filePath = $filePath;
		$this->syntaxTree = $syntaxTree;
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}

	public function addBreakpoint(Breakpoint $breakpoint) {
		$this->breakpoints[] = $breakpoint;
	}

	/**
	 * @return Breakpoint[]
	 */
	public function getBreakpoints() {
		return $this->breakpoints;
	}

	/**
	 * Serializes this object and returns the representation.
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize($this);
	}

}

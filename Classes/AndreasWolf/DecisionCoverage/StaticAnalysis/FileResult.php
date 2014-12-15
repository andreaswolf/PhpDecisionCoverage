<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\SyntaxTree;
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
	 * @var SyntaxTree
	 */
	protected $syntaxTree;

	/**
	 * @var Probe[]
	 */
	protected $breakpoints = array();


	public function __construct($filePath, SyntaxTree $syntaxTree) {
		$this->filePath = $filePath;
		$this->syntaxTree = $syntaxTree;
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}

	public function addBreakpoint(Probe $breakpoint) {
		$this->breakpoints[] = $breakpoint;
	}

	/**
	 * @return Probe[]
	 */
	public function getBreakpoints() {
		return $this->breakpoints;
	}

	/**
	 * @return SyntaxTree
	 */
	public function getSyntaxTree() {
		return $this->syntaxTree;
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

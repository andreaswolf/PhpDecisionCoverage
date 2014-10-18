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

	public function addBreakpoint(Breakpoint $breakpoint) {
		$this->breakpoints[] = $breakpoint;
	}

	/**
	 * @return Breakpoint[]
	 */
	public function getBreakpoints() {
		return $this->breakpoints;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\WatchedValue;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;


/**
 * A data collection point where various values should be watched.
 *
 * Is attached to a syntax tree node.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Breakpoint {

	/**
	 * @var int
	 */
	protected $line;


	/**
	 * @param int $line
	 */
	public function __construct($line) {
		$this->line = $line;
	}

	/**
	 * @return int
	 */
	public function getLine() {
		return $this->line;
	}

}

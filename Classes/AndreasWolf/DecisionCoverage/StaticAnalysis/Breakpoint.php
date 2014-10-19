<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;

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
	 * The expressions that should be watched
	 *
	 * @var Expr[]
	 */
	protected $watchedExpressions = array();


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

	/**
	 * @param Expr $watcher
	 */
	public function addWatchedExpression(Expr $watcher) {
		$this->watchedExpressions[] = $watcher;
	}

	/**
	 * @return Expr[]
	 */
	public function getWatchedExpressions() {
		return $this->watchedExpressions;
	}

}

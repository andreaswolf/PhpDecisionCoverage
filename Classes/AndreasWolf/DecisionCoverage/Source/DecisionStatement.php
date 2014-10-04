<?php
namespace AndreasWolf\DecisionCoverage\Source;

use PhpParser\Node\Stmt;


/**
 * Wrapper for a decision statement.
 *
 * Besides the statement it also holds the context (class/method) the statement is in.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
abstract class DecisionStatement {

	/**
	 * @var Stmt
	 */
	protected $conditional;

	/**
	 * @var StatementContext
	 */
	protected $context;

	/**
	 * @param Stmt $decision
	 * @param StatementContext $context
	 */
	public function __construct(Stmt $decision, StatementContext $context) {
		// TODO extract information from conditional and throw it away - otherwise we might have large parts of the AST
		// here we don't really need
		$this->conditional = $decision;
		$this->context = $context;
	}

	/**
	 * @return \AndreasWolf\DecisionCoverage\Source\StatementContext
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * Returns the line this statement is in
	 *
	 * @return int
	 */
	public function getLine() {
		return $this->conditional->getLine();
	}

}

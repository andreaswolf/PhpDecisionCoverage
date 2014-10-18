<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Breakpoint;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileAnalysis;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\NodeVisitor;
use PhpParser\Node;


/**
 * Node visitor that creates breakpoints for each statement where values should be fetched.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BreakpointFactory implements NodeVisitor {

	/**
	 * @var FileAnalysis
	 */
	protected $analysis;

	public function __construct(FileAnalysis $analysis) {
		$this->analysis = $analysis;
	}

	/**
	 * Signal for the start of an instrumentation run.
	 *
	 * @param Node[] $rootNodes
	 * @return void
	 */
	public function startInstrumentation($rootNodes) {
	}

	/**
	 * Signal for the end of an instrumentation run.
	 *
	 * @param Node[] $rootNodes
	 * @return void
	 */
	public function endInstrumentation($rootNodes) {
	}

	/**
	 * @param Node $node
	 * @return Node
	 */
	public function handleNode(Node $node) {
		if (!in_array($node->getType(), array('Stmt_If'))) {
			return;
		}

		$breakpoint = $this->createBreakpoint($node);
		$this->addWatchExpressionsToBreakpoint($breakpoint, $node->cond);

		$this->analysis->addBreakpoint($breakpoint);
	}

	/**
	 * @param Breakpoint $breakpoint
	 * @param Node $rootNode
	 */
	protected function addWatchExpressionsToBreakpoint(Breakpoint $breakpoint, Node $rootNode) {
		$nodeIterator = new \RecursiveIteratorIterator(
			new SyntaxTreeIterator(array($rootNode), TRUE), \RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($nodeIterator as $node) {
			if (in_array($node->getType(), array('Expr_Variable'))) {
				$breakpoint->addWatchedExpression($node);
			}
		}
	}

	/**
	 * @param Node $node
	 * @return Breakpoint
	 */
	protected function createBreakpoint(Node $node) {
		return new Breakpoint($node->getLine());
	}

}

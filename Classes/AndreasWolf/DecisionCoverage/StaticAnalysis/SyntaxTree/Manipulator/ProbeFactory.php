<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\NodeVisitor;
use PhpParser\Node;


/**
 * Node visitor that creates breakpoints for each statement where values should be fetched.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ProbeFactory implements NodeVisitor {

	/**
	 * @var FileResult
	 */
	protected $analysis;

	public function __construct(FileResult $analysis) {
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
		if (!in_array($node->getType(), array('Stmt_If', 'Stmt_ElseIf'))) {
			return;
		}

		$breakpoint = $this->createBreakpoint($node);
		$this->addWatchExpressionsToBreakpoint($breakpoint, $node->cond);

		$this->analysis->addBreakpoint($breakpoint);
	}

	/**
	 * @param Probe $probe
	 * @param Node $rootNode
	 */
	protected function addWatchExpressionsToBreakpoint(Probe $probe, Node $rootNode) {
		$nodeIterator = new \RecursiveIteratorIterator(
			new SyntaxTreeIterator(array($rootNode), TRUE), \RecursiveIteratorIterator::SELF_FIRST
		);

		/** @var Node $node */
		foreach ($nodeIterator as $node) {
			if (in_array($node->getType(), array('Expr_Variable', 'Expr_PropertyFetch', 'Expr_StaticPropertyFetch',
				'Expr_MethodCall', 'Expr_StaticCall'))
			) {

				$probe->addWatchedExpression($node);
			}
		}
	}

	/**
	 * @param Node $node
	 * @return Probe
	 */
	protected function createBreakpoint(Node $node) {
		$probe = new Probe($node->getLine());
		$node->setAttribute('coverage__probe', $probe);

		return $probe;
	}

}

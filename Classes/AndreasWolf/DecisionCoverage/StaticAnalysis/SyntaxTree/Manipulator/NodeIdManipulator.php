<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\NodeVisitor;
use PhpParser\Node;


/**
 * Adds a tree-unique node id to each traversed node of a syntax tree.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class NodeIdManipulator implements NodeVisitor {

	protected $currentId;

	/**
	 * Signal for the start of an instrumentation run.
	 *
	 * @param Node[] $rootNodes
	 * @return void
	 */
	public function startInstrumentation($rootNodes) {
		$this->currentId = 0;
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
		// use coverage__ as a kind of "pseudo-namespace" to not interfere with other attributes that might already
		// be set
		$node->setAttribute('coverage__nodeId', ++$this->currentId);
	}

}

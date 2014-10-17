<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree;
use PhpParser\Node;


/**
 * Interface for a component that visits nodes of an abstract syntax tree, e.g. to extract information from them
 * or change them.
 *
 * Note that this is not a strict implementation of the GoF visitor pattern, as the visited elements (= the syntax
 * tree nodes) don’t need to have a method to accept the visitor. Instead, we manipulate them from the outside, as
 * a rather "hostile" visitor. Nevertheless, it serves the purpose…
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface NodeVisitor {

	/**
	 * Signal for the start of an instrumentation run.
	 *
	 * @param Node[] $rootNodes
	 * @return void TODO decide on this
	 */
	public function startInstrumentation($rootNodes);

	/**
	 * Signal for the end of an instrumentation run.
	 *
	 * @param Node[] $rootNodes
	 * @return void TODO decide on this
	 */
	public function endInstrumentation($rootNodes);

	/**
	 * @param Node $node
	 * @return Node
	 */
	public function handleNode(Node $node);

}

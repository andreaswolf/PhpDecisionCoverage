<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree;

use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use PhpParser\Node;


/**
 * An instrumenter modifies a given Abstract Syntax Tree (AST) using the configured manipulators.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Instrumenter {

	/**
	 * @var NodeVisitor[]
	 */
	protected $visitors;

	/**
	 * @param Node[] $nodes
	 */
	public function instrument(&$nodes) {
		$iterator = new \RecursiveIteratorIterator(
			new SyntaxTreeIterator($nodes, TRUE), \RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($this->visitors as $manipulator) {
			$manipulator->startInstrumentation($nodes);
		}

		foreach ($iterator as $currentNode) {
			foreach ($this->visitors as $manipulator) {
				$manipulator->handleNode($currentNode);
			}
		}

		foreach ($this->visitors as $manipulator) {
			$manipulator->endInstrumentation($nodes);
		}
	}

	/**
	 * @param NodeVisitor $visitor
	 * @param int $precedence The precedence of this manipulator. The lower, the sooner this manipulator will be called.
	 */
	public function addVisitor(NodeVisitor $visitor, $precedence = 0) {
		$this->visitors[$precedence] = $visitor;
	}

}

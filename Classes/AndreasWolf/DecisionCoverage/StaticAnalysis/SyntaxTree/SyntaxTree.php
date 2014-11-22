<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree;

use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use PhpParser\Node;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SyntaxTree {

	/**
	 * @var Node[]
	 */
	protected $rootNodes;


	/**
	 * @param Node[] $nodes
	 */
	public function __construct($nodes) {
		$this->rootNodes = $nodes;
	}

	/**
	 * @return \RecursiveIteratorIterator
	 */
	public function getIterator() {
		return new \RecursiveIteratorIterator(
			new SyntaxTreeIterator($this->rootNodes, TRUE), \RecursiveIteratorIterator::SELF_FIRST
		);
	}

}

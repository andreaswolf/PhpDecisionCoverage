<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\NodeIdManipulator;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class NodeIdManipulatorTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function idIsAddedAsAttributeToNode() {
		$manipulator = new NodeIdManipulator();

		$node = $this->getMock('PhpParser\Node');
		$node->expects($this->once())->method('setAttribute')->with($this->equalTo('coverage__nodeId'), $this->equalTo(1));

		$manipulator->startInstrumentation($node);
		$manipulator->handleNode($node);
	}

	/**
	 * @test
	 */
	public function consecutiveIdsAreAddedToConsecutiveNodes() {
		$manipulator = new NodeIdManipulator();

		$node1 = $this->getMock('PhpParser\Node');
		$node1->expects($this->once())->method('setAttribute')->with($this->equalTo('coverage__nodeId'), $this->equalTo(1));
		$node2 = $this->getMock('PhpParser\Node');
		$node2->expects($this->once())->method('setAttribute')->with($this->equalTo('coverage__nodeId'), $this->equalTo(2));

		$manipulator->startInstrumentation($node1);
		$manipulator->handleNode($node1);
		$manipulator->handleNode($node2);
	}

}

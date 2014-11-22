<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\NodeIdGenerator;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class NodeIdGeneratorTest extends UnitTestCase {

	protected function mockUuidService() {
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\Service\UuidService')->getMock();
	}

	/**
	 * @test
	 */
	public function idIsAddedAsAttributeToNode() {
		$manipulator = new NodeIdGenerator();

		$node = $this->getMock('PhpParser\Node');
		$node->expects($this->once())->method('setAttribute')->with($this->equalTo('coverage__nodeId'), $this->logicalNot($this->isEmpty()));

		$manipulator->startInstrumentation($node);
		$manipulator->handleNode($node);
	}

	/**
	 * @test
	 */
	public function consecutiveIdsAreAddedToConsecutiveNodes() {
		$uuidService = $this->mockUuidService();
		$uuidService->expects($this->exactly(2))->method('uuid4')->will($this->onConsecutiveCalls(
			$this->returnValue('A'),
			$this->returnValue('B')
		));
		$manipulator = new NodeIdGenerator($uuidService);

		$node1 = $this->getMock('PhpParser\Node');
		$node1->expects($this->once())->method('setAttribute')->with($this->equalTo('coverage__nodeId'), $this->equalTo('A'));
		$node2 = $this->getMock('PhpParser\Node');
		$node2->expects($this->once())->method('setAttribute')->with($this->equalTo('coverage__nodeId'), $this->equalTo('B'));

		$manipulator->startInstrumentation($node1);
		$manipulator->handleNode($node1);
		$manipulator->handleNode($node2);
	}

}

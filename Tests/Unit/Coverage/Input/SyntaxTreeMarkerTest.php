<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Input;

use AndreasWolf\DecisionCoverage\Coverage\Input\SyntaxTreeMarker;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;


class SyntaxTreeMarkerTest extends UnitTestCase {

	/**
	 * @return array
	 */
	protected function getTreeForNodes($rootNode) {
		$subject = new SyntaxTreeMarker();

		return $subject->markSyntaxTree($rootNode);
	}

	/**
	 * @return array
	 */
	protected function getTreeForSimpleBooleanAnd() {
		return $this->getTreeForNodes($this->createBooleanAnd('A',
			$this->mockCondition('B'),
			$this->mockCondition('C')
		));
	}

	/**
	 * @test
	 */
	public function rootNodeGetsOneAsLeftId() {
		$markedTree = $this->getTreeForSimpleBooleanAnd();

		$this->assertEquals(1, $markedTree[0]['l']);
	}

	/**
	 * @test
	 */
	public function idsOfMarkerNodesAreCorrectlyAssignedForSimpleTrees() {
		$markedTree = $this->getTreeForSimpleBooleanAnd();

		$this->assertEquals('A', $markedTree[0]['id']);
		$this->assertEquals('B', $markedTree[1]['id']);
		$this->assertEquals('C', $markedTree[2]['id']);
	}

	/**
	 * @test
	 */
	public function leftSubnodeOfSimpleTreeHasCorrectLeftId() {
		$markedTree = $this->getTreeForSimpleBooleanAnd();

		$this->assertEquals(2, $markedTree[1]['l']);
	}

	/**
	 * @test
	 */
	public function rightSubnodeOfSimpleTreeHasNoLeftAndCorrectRightId() {
		$markedTree = $this->getTreeForSimpleBooleanAnd();

		$this->assertArrayNotHasKey('l', $markedTree[2]);
		$this->assertEquals(3, $markedTree[2]['r']);
	}

	/**
	 * @test
	 */
	public function rootNodeHasCorrectRightId() {
		$markedTree = $this->getTreeForSimpleBooleanAnd();

		$this->assertEquals(4, $markedTree[0]['r']);
	}

	/**
	 * @test
	 */
	public function rootOfSubtreeHasCorrectRightId() {
		$markedTree = $this->getTreeForNodes($this->createBooleanAnd('A',
			$this->createBooleanAnd('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		));

		$this->assertEquals(5, $markedTree[1]['r']);
	}

	/**
	 * @test
	 */
	public function rootOfRightSubtreeHasCorrectLeftAndRightValues() {
		$markedTree = $this->getTreeForNodes($this->createBooleanAnd('A',
			$this->mockCondition('B'),
			$this->createBooleanAnd('C',
				$this->mockCondition('D'),
				$this->mockCondition('E')
			)
		));

		$this->assertEquals(3, $markedTree[2]['l']);
		$this->assertEquals(6, $markedTree[2]['r']);
	}

	/**
	 * @test
	 */
	public function idsOfMarkerNodesAreCorrectlyAssignedForTreeWithNestedBooleanAnd() {
		$markedTree =$this->getTreeForNodes($this->createBooleanAnd('A',
			$this->createBooleanAnd('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		));

		$this->assertEquals('A', $markedTree[0]['id']);
		$this->assertEquals('B', $markedTree[1]['id']);
		$this->assertEquals('C', $markedTree[2]['id']);
		$this->assertEquals('D', $markedTree[3]['id']);
		$this->assertEquals('E', $markedTree[4]['id']);
	}

	/**
	 * @test
	 */
	public function noIdsAreAssignedToChildrenOfEqual() {
		$markedTree = $this->getTreeForNodes($this->createBooleanAnd('A',
			new Expr\BinaryOp\Equal(
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		));

		$this->assertEquals(1, $markedTree[0]['l']);
		$this->assertEquals(4, $markedTree[0]['r']);
		$this->assertEquals(2, $markedTree[1]['l']);
		$this->assertEquals(3, $markedTree[2]['r']);
	}

	/**
	 * @test
	 */
	public function idsAreCorrectlyAssignedForBinaryOperationCondition() {
		$markedTree = $this->getTreeForNodes($this->createBooleanAnd('A',
			$this->mockBinaryCondition('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		));

		$this->assertEquals('A', $markedTree[0]['id']);
		$this->assertEquals('B', $markedTree[1]['id']);
		$this->assertEquals('E', $markedTree[2]['id']);
	}

	/**
	 * @test
	 */
	public function leftAndRightValuesAreCorrectlyAssignedForBinaryConditions() {
		$markedTree = $this->getTreeForNodes($this->createBooleanAnd('A',
			$this->mockBinaryCondition('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		));

		$this->assertEquals(1, $markedTree[0]['l']);
		$this->assertEquals(4, $markedTree[0]['r']);
		$this->assertEquals(2, $markedTree[1]['l']);
		$this->assertEquals(3, $markedTree[2]['r']);
	}


	/**
	 * @param Expr $left
	 * @param Expr $right
	 * @return Expr\BinaryOp\Equal
	 */
	protected function createEqualCondition($left, $right) {
		return new Expr\BinaryOp\Equal($left, $right);
	}

	protected function createBooleanAnd($nodeId, $left, $right) {
		$expression = new Expr\BinaryOp\BooleanAnd($left, $right);
		$expression->setAttribute('coverage__nodeId', $nodeId);

		return $expression;
	}

	protected function mockCondition($nodeId) {
		$mock = $this->getMockBuilder('PhpParser\Node\Expr')->getMock();
		$mock->expects($this->any())->method('getSubNodeNames')->willReturn(array());
		$mock->expects($this->any())->method('getAttribute')->with('coverage__nodeId')->willReturn($nodeId);
		$mock->expects($this->any())->method('getType')->willReturn('Expr');

		return $mock;
	}

	protected function mockBinaryCondition($nodeId, $left, $right) {
		$mock = $this->getMockBuilder('PhpParser\Node\Expr\BinaryOp')->setConstructorArgs(array($left, $right))->getMock();
		$mock->expects($this->any())->method('getSubNodeNames')->willReturn(array('left', 'right'));
		$mock->expects($this->any())->method('getAttribute')->with('coverage__nodeId')->willReturn($nodeId);
		$mock->expects($this->any())->method('getType')->willReturn('Expr_BinaryOp');

		return $mock;
	}

}

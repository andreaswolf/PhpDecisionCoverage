<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Builder\DecisionInputBuilder;
use AndreasWolf\DecisionCoverage\Coverage\Input\SyntaxTreeMarker;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DecisionInputBuilderTest extends UnitTestCase {

	/**
	 * @return Expr\BinaryOp
	 */
	protected function getSimpleBooleanAnd() {
		return $this->createBooleanAnd('A',
			$this->mockCondition('B'),
			$this->mockCondition('C')
		);
	}


	/**
	 * @test
	 */
	public function resultForSingleBooleanAndHasCorrectNumberOfInputs() {
		$tree = $this->getSimpleBooleanAnd();
		$subject = new DecisionInputBuilder($tree);

		$inputs = $subject->buildInput();

		$this->assertCount(3, $inputs);
	}

	/**
	 * @test
	 */
	public function resultForSingleBooleanAndHasCorrectInputs(){
		$tree = $this->getSimpleBooleanAnd();
		$subject = new DecisionInputBuilder($tree);

		$inputs = $subject->buildInput();

		$this->assertEquals(array('B' => TRUE, 'C' => TRUE), $inputs[0]->getInputs());
		$this->assertEquals(array('B' => TRUE, 'C' => FALSE), $inputs[1]->getInputs());
		$this->assertEquals(array('B' => FALSE), $inputs[2]->getInputs());
	}

	/**
	 * @test
	 */
	public function resultForBooleanAndWithNestedBooleanOrHasCorrectNumberOfInputs() {
		$tree = $this->createBooleanAnd('A',
			$this->createBooleanOr('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		);
		$subject = new DecisionInputBuilder($tree);

		$inputs = $subject->buildInput();

		$this->assertCount(5, $inputs);
	}

	/**
	 * @test
	 */
	public function resultForBooleanOrWithNestedBooleanAndHasCorrectNumberOfInputs() {
		$tree = $this->createBooleanOr('A',
			$this->createBooleanAnd('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		);
		$subject = new DecisionInputBuilder($tree);

		$inputs = $subject->buildInput();

		$this->assertCount(5, $inputs);
	}

	/**
	 * @test
	 */
	public function resultForBooleanOrWithNestedBooleanAndHasCorrectInputs() {
		$tree = $this->createBooleanOr('A',
			$this->createBooleanAnd('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		);
		$subject = new DecisionInputBuilder($tree);

		$inputs = $subject->buildInput();

		$this->assertEquals(array('C' => TRUE, 'D' => TRUE), $inputs[0]->getInputs());
		$this->assertEquals(array('C' => TRUE, 'D' => FALSE, 'E' => TRUE), $inputs[1]->getInputs());
		$this->assertEquals(array('C' => TRUE, 'D' => FALSE, 'E' => FALSE), $inputs[2]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'E' => TRUE), $inputs[3]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'E' => FALSE), $inputs[4]->getInputs());
	}

	/**
	 * @test
	 */
	public function resultForBooleanAndWithNestedBooleanOrHasCorrectInputs() {
		$tree = $this->createBooleanAnd('A',
			$this->createBooleanOr('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		);
		$subject = new DecisionInputBuilder($tree);

		$inputs = $subject->buildInput();

		$this->assertEquals(array('C' => TRUE, 'E' => TRUE), $inputs[0]->getInputs());
		$this->assertEquals(array('C' => TRUE, 'E' => FALSE), $inputs[1]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => TRUE, 'E' => TRUE), $inputs[2]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => TRUE, 'E' => FALSE), $inputs[3]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => FALSE), $inputs[4]->getInputs());
	}

	/**
	 * @test
	 */
	public function resultForBooleanAndWithTwoNestedBooleanOrsHasCorrectInputs() {
		$tree = $this->createBooleanAnd('A',
			$this->createBooleanOr('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->createBooleanOr('E',
				$this->mockCondition('F'),
				$this->mockCondition('G')
			)
		);
		$subject = new DecisionInputBuilder($tree);

		$inputs = $subject->buildInput();

		$this->assertCount(7, $inputs);
		$this->assertEquals(array('C' => TRUE, 'F' => TRUE), $inputs[0]->getInputs());
		$this->assertEquals(array('C' => TRUE, 'F' => FALSE, 'G' => TRUE), $inputs[1]->getInputs());
		$this->assertEquals(array('C' => TRUE, 'F' => FALSE, 'G' => FALSE), $inputs[2]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => TRUE, 'F' => TRUE), $inputs[3]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => TRUE, 'F' => FALSE, 'G' => TRUE), $inputs[4]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => TRUE, 'F' => FALSE, 'G' => FALSE), $inputs[5]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => FALSE), $inputs[6]->getInputs());
	}


	protected function createBooleanAnd($nodeId, $left, $right) {
		$expression = new Expr\BinaryOp\BooleanAnd($left, $right);
		$expression->setAttribute('coverage__nodeId', $nodeId);

		return $expression;
	}

	protected function createBooleanOr($nodeId, $left, $right) {
		$expression = new Expr\BinaryOp\BooleanOr($left, $right);
		$expression->setAttribute('coverage__nodeId', $nodeId);

		return $expression;
	}

	protected function mockCondition($nodeId) {
		$mock = $this->getMockBuilder('PhpParser\Node\Expr')->getMock();
		$mock->expects($this->any())->method('getSubNodeNames')->willReturn(array());
		$mock->expects($this->any())->method('getAttribute')->with('coverage__nodeId')->willReturn($nodeId);

		return $mock;
	}

}

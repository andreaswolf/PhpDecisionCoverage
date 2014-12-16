<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Builder\DecisionInputBuilder;
use AndreasWolf\DecisionCoverage\Coverage\Input\SyntaxTreeMarker;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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
		$subject = new DecisionInputBuilder();

		$inputs = $subject->buildInput($tree);

		$this->assertCount(3, $inputs);
	}

	/**
	 * @test
	 */
	public function resultForSingleBooleanAndHasCorrectInputs(){
		$tree = $this->getSimpleBooleanAnd();
		$subject = $this->getDecisionBuilder();

		$inputs = $subject->buildInput($tree);

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
		$subject = new DecisionInputBuilder();

		$inputs = $subject->buildInput($tree);

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
		$subject = new DecisionInputBuilder();

		$inputs = $subject->buildInput($tree);

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
		$subject = new DecisionInputBuilder();

		$inputs = $subject->buildInput($tree);

		$this->assertEquals(array('C' => TRUE, 'D' => TRUE), $inputs[0]->getInputs());
		$this->assertEquals(array('C' => TRUE, 'D' => FALSE, 'E' => TRUE), $inputs[1]->getInputs());
		$this->assertEquals(array('C' => TRUE, 'D' => FALSE, 'E' => FALSE), $inputs[2]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'E' => TRUE), $inputs[3]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'E' => FALSE), $inputs[4]->getInputs());
	}

	/**
	 * @test
	 */
	public function resultsForDecisionsInBooleanOrWithNestedBooleanAndAreAddedToTheInputObject(){
		$tree = $this->createBooleanOr('A',
			$this->createBooleanAnd('B',
				$this->mockCondition('C'),
				$this->mockCondition('D')
			),
			$this->mockCondition('E')
		);
		$subject = $this->getDecisionBuilder();

		$inputs = $subject->buildInput($tree);

		// test B first because of evaluation order
		$this->assertEquals(TRUE, $inputs[0]->getValueForCondition('B'));
		$this->assertEquals(TRUE, $inputs[0]->getValueForCondition('A'));
		$this->assertEquals(FALSE, $inputs[3]->getValueForCondition('B'));
		$this->assertEquals(TRUE, $inputs[3]->getValueForCondition('A'));
		$this->assertEquals(FALSE, $inputs[4]->getValueForCondition('B'));
		$this->assertEquals(FALSE, $inputs[4]->getValueForCondition('A'));
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
		$subject = $this->getDecisionBuilder();

		$inputs = $subject->buildInput($tree);

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
		$subject = $this->getDecisionBuilder();

		$inputs = $subject->buildInput($tree);

		$this->assertCount(7, $inputs);
		$this->assertEquals(array('C' => TRUE, 'F' => TRUE), $inputs[0]->getInputs());
		$this->assertEquals(array('C' => TRUE, 'F' => FALSE, 'G' => TRUE), $inputs[1]->getInputs());
		$this->assertEquals(array('C' => TRUE, 'F' => FALSE, 'G' => FALSE), $inputs[2]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => TRUE, 'F' => TRUE), $inputs[3]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => TRUE, 'F' => FALSE, 'G' => TRUE), $inputs[4]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => TRUE, 'F' => FALSE, 'G' => FALSE), $inputs[5]->getInputs());
		$this->assertEquals(array('C' => FALSE, 'D' => FALSE), $inputs[6]->getInputs());
	}

	/**
	 * @test
	 */
	public function rootDecisionResultInDeeplyNestedStructureIsEvaluatedCorrectly() {
		$tree = $this->createBooleanAnd('A',
			$this->createBooleanOr('B',
				$this->createBooleanAnd('C',
					$this->createBooleanAnd('D',
						$this->mockCondition('E'),
						$this->mockCondition('F')
					),
					$this->mockCondition('G')
				),
				$this->mockCondition('H')
			),
			$this->mockCondition('I')
		);
		$subject = $this->getDecisionBuilder();

		$inputs = $subject->buildInput($tree);

		$this->assertEquals(TRUE, $inputs[0]->getValueForCondition('A'));
		$this->assertEquals(FALSE, $inputs[10]->getValueForCondition('A'));
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

	/**
	 * @param bool $enableLogging
	 * @return DecisionInputBuilder
	 */
	protected function getDecisionBuilder($enableLogging = FALSE) {
		if ($enableLogging) {
			$subject = new DecisionInputBuilder(new Logger('Test', [new StreamHandler(STDOUT)]));
		} else {
			$subject = new DecisionInputBuilder();
		}

		return $subject;
	}

}

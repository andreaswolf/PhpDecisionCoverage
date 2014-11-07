<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\LogicalOrCoverage;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\BreakpointDataSet;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;


class LogicalOrCoverageTest extends UnitTestCase {

	/**
	 * @var LogicalOrCoverage
	 */
	protected $subject;

	/**
	 * @var Expr[]
	 */
	protected $expressions;


	public function setUp() {
		$this->expressions = array($this->mockSingleBooleanExpression(1), $this->mockSingleBooleanExpression(2));
		$logicalOr = new LogicalOr($this->expressions[0], $this->expressions[1]);
		$this->subject = new LogicalOrCoverage($logicalOr);
	}

	/**
	 * @test
	 */
	public function coverageIsZeroWithoutDataSets() {
		$this->assertSame(0.0, $this->subject->getCoverage());
	}

	/**
	 * Provides one data set each that should cover the logical AND for one third (regardless of the exact shape).
	 */
	public function singleExpressionDataSetProvider() {
		return array(
			'FALSE/FALSE' => array(FALSE, FALSE),
			'TRUE/FALSE' => array(TRUE, FALSE),
			'FALSE/TRUE' => array(FALSE, TRUE),
			'TRUE/TRUE' => array(TRUE, TRUE),
		);
	}

	/**
	 * @test
	 * @dataProvider singleExpressionDataSetProvider
	 */
	public function oneDataSetCoversExpressionForOneThird($leftValue, $rightValue) {
		$this->subject->recordCoveredInput($this->createExpressionDataSet($leftValue, $rightValue));

		$this->assertSame(0.33, $this->subject->getCoverage());
	}

	/**
	 * @test
	 * @dataProvider singleExpressionDataSetProvider
	 */
	public function addingTheSameDataSetAgainDoesNotIncreaseCoverage($leftValue, $rightValue) {
		$this->subject->recordCoveredInput($this->createExpressionDataSet($leftValue, $rightValue));
		$this->subject->recordCoveredInput($this->createExpressionDataSet($leftValue, $rightValue));

		$this->assertSame(0.33, $this->subject->getCoverage());
	}

	/**
	 * This tests that both values that will appear the same to PHP because of short circuit evaluation logic.
	 * @test
	 */
	public function dontCareInShortCircuitCombinationIsIgnored() {
		$this->subject->recordCoveredInput($this->createExpressionDataSet(TRUE, TRUE));
		$this->subject->recordCoveredInput($this->createExpressionDataSet(TRUE, FALSE));

		$this->assertSame(0.33, $this->subject->getCoverage());
	}

	/**
	 * @test
	 */
	public function conditionWithAllThreeInputCategoriesCoveredIsFullyCovered() {
		$this->subject->recordCoveredInput($this->createExpressionDataSet(TRUE, TRUE));
		$this->subject->recordCoveredInput($this->createExpressionDataSet(FALSE, TRUE));
		$this->subject->recordCoveredInput($this->createExpressionDataSet(FALSE, FALSE));

		$this->assertSame(1.00, $this->subject->getCoverage());
	}



	/**
	 * Creates a data set for the expression with the given values for the expressions.
	 *
	 * @param boolean $leftValue
	 * @param boolean $rightValue
	 * @return BreakpointDataSet
	 */
	protected function createExpressionDataSet($leftValue, $rightValue) {
		$dataSet = $this->getDataSetForMockedBreakpoint();
		$dataSet->addValue($this->expressions[0], new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, $leftValue));
		$dataSet->addValue($this->expressions[1], new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, $rightValue));

		return $dataSet;
	}

	/**
	 * @param $nodeId
	 * @return Expr
	 */
	protected function mockSingleBooleanExpression($nodeId) {
		$mock = $this->getMockBuilder('PhpParser\Node\Expr')->disableOriginalConstructor()->getMock();
		$mock->expects($this->any())->method('getAttribute')->with('coverage__nodeId')->will($this->returnValue($nodeId));
		return $mock;
	}

	/**
	 *
	 */
	protected function getBreakpointDataSet($expressions) {
		$dataSet = $this->getDataSetForMockedBreakpoint();
		// TODO implement
	}

	/**
	 * @return BreakpointDataSet
	 */
	protected function getDataSetForMockedBreakpoint() {
		return new BreakpointDataSet(
			$this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\Breakpoint')->disableOriginalConstructor()->getMock()
		);
	}

}
 
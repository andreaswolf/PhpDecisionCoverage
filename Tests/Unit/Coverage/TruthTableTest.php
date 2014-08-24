<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\TruthTable;
use AndreasWolf\DecisionCoverage\Tests\Unit\ParserBasedTestCase;
use PhpParser\Lexer;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Stmt\If_;
use PhpParser\Parser;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TruthTableTest extends ParserBasedTestCase {

	/**
	 * @test
	 */
	public function allExpressionsInMultipleConditionIfStatementAreParsed() {
		$parsedNodes = $this->parseCode('if ($variable == TRUE && $foo == "bar" && !($baz < 10)) {}');

		$decisionTable = new TruthTable($parsedNodes[0]);

		$testedExpressions = $decisionTable->getExpressions();
		$this->assertCount(3, $testedExpressions);
	}

	/**
	 * @test
	 */
	public function expressionsCanBeRetrievedGroupedByVariableName() {
		$parsedNodes = $this->parseCode('if ($variable == TRUE && $foo == "bar" && !($baz < 10)) {}');
		$decisionTable = new TruthTable($parsedNodes[0]);

		$testedExpressions = $decisionTable->getExpressionsByVariableName();
		$this->assertCount(3, $testedExpressions);
		$this->assertEquals(array('variable', 'foo', 'baz'), array_keys($testedExpressions));
	}

	/**
	 * @test
	 */
	public function variableNamesAreCorrectlyExtractedFromConditions() {
		$parsedNodes = $this->parseCode('if ($variable == TRUE && $foo == "bar" && !($baz < 10)) {}');

		$decisionTable = new TruthTable($parsedNodes[0]);

		$coveredVariableNames = $decisionTable->getCoveredVariableNames();
		$this->assertCount(3, $coveredVariableNames);
		$this->assertEquals(array('variable', 'foo', 'baz'), $coveredVariableNames);
	}

	/**
	 * @test
	 */
	public function variableNamesAreCorrectlyExtractedIfVariablesAreComparedWithEachOther() {
		$parsedNodes = $this->parseCode('if ($foo == $bar) {}');

		$decisionTable = new TruthTable($parsedNodes[0]);

		$coveredVariableNames = $decisionTable->getCoveredVariableNames();
		$this->assertCount(2, $coveredVariableNames);
		$this->assertEquals(array('foo', 'bar'), $coveredVariableNames);
	}

	/**
	 * @test
	 */
	public function variableNameIsOnlyReturnedOnceEvenIfComparedMultipleTimes() {
		$parsedNodes = $this->parseCode('if ($baz > 10 || $baz < 0) {}');

		$decisionTable = new TruthTable($parsedNodes[0]);

		$coveredVariableNames = $decisionTable->getCoveredVariableNames();
		$this->assertCount(1, $coveredVariableNames);
		$this->assertEquals(array('baz'), $coveredVariableNames);
	}

}

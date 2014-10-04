<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Source;
use AndreasWolf\DecisionCoverage\Source\IfStatement;
use AndreasWolf\DecisionCoverage\Source\StatementContext;
use AndreasWolf\DecisionCoverage\Tests\ParserBasedTestCase;


/**
 * Created by PhpStorm.
 * User: aw
 * Date: 23.08.14
 * Time: 13:31
 */
class IfStatementTest extends ParserBasedTestCase {

	protected function mockStatementContext() {
		return new StatementContext('-');
	}

	/**
	 * @test
	 */
	public function simpleComparisonAsConditionIsCorrectlyExtracted() {
		$parsedNodes = $this->parseCode('if ($foo == "bar") {}');

		$ifStatement = new IfStatement($parsedNodes[0], $this->mockStatementContext());

		$this->assertEquals(1, $ifStatement->getConditionBlockCount());
		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition', $ifStatement->getConditionBlock(0));
	}

	/**
	 * @test
	 */
	public function simpleIfStatementHasNoElseBlock() {
		$parsedNodes = $this->parseCode('if ($foo == "bar") {}');

		$ifStatement = new IfStatement($parsedNodes[0], $this->mockStatementContext());

		$this->assertFalse($ifStatement->hasElseBlock());
	}

	/**
	 * @test
	 */
	public function singleVariableAsConditionIsCorrectlyExtracted() {
		$parsedNodes = $this->parseCode('if ($foo) {}');

		$ifStatement = new IfStatement($parsedNodes[0], $this->mockStatementContext());

		$this->assertEquals(1, $ifStatement->getConditionBlockCount());
		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition', $ifStatement->getConditionBlock(0));
	}

	/**
	 * @test
	 */
	public function elseIfPartIsCorrectlyExtracted() {
		$parsedNodes = $this->parseCode('if ($foo < 10) {} elseif ($foo > 20) {}');

		$ifStatement = new IfStatement($parsedNodes[0], $this->mockStatementContext());

		$this->assertEquals(2, $ifStatement->getConditionBlockCount());
		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition', $ifStatement->getConditionBlock(0));
		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition', $ifStatement->getConditionBlock(1));
	}

	/**
	 * @test
	 */
	public function multipleElseIfPartsAreCorrectlyExtracted() {
		$parsedNodes = $this->parseCode('if ($foo < 10) {} elseif ($foo > 20) {} elseif ($bar == "foo") {}');

		$ifStatement = new IfStatement($parsedNodes[0], $this->mockStatementContext());

		$this->assertEquals(3, $ifStatement->getConditionBlockCount());
		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition', $ifStatement->getConditionBlock(0));
		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition', $ifStatement->getConditionBlock(1));
		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition', $ifStatement->getConditionBlock(2));
	}

	/**
	 * @test
	 */
	public function elsePartIsRecognized() {
		$parsedNodes = $this->parseCode('if ($foo < 10) {} else {}');

		$ifStatement = new IfStatement($parsedNodes[0], $this->mockStatementContext());

		$this->assertEquals(1, $ifStatement->getConditionBlockCount());
		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition', $ifStatement->getConditionBlock(0));
		$this->assertTrue($ifStatement->hasElseBlock());
	}

}

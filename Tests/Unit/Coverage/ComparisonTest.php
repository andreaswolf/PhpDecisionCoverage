<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage;

use AndreasWolf\DecisionCoverage\Coverage\Comparison;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;


class ComparisonTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function canonicalizeReturnsUnchangedEqualsExpressionIfVariableIsOnTheLeftSide() {
		$var = $this->mockVariable('foo');
		$value = $this->mockInteger(5);
		$expression = new Expr\BinaryOp\Equal($var, $value);

		$this->assertSame($expression, Comparison::canonicalize($expression));
	}

	/**
	 * @test
	 */
	public function canonicalizeReturnsUnchangedEqualsExpressionIfThereAreTwoVariables() {
		$varL = $this->mockVariable('foo');
		$varR = $this->mockVariable('bar');
		$expression = new Expr\BinaryOp\Equal($varL, $varR);

		$this->assertSame($expression, Comparison::canonicalize($expression));
	}

	/**
	 * @test
	 */
	public function canonicalizeReturnsUnchangedGreaterExpressionIfVariableIsOnTheLeftSide() {
		$var = $this->mockVariable('foo');
		$value = $this->mockInteger(5);
		$expression = new Expr\BinaryOp\Greater($var, $value);

		$this->assertSame($expression, Comparison::canonicalize($expression));
	}

	/**
	 * @test
	 */
	public function canonicalizedFormReversesOrderForYodaNotation() {
		$var = $this->mockVariable('foo');
		$value = $this->mockInteger(5);
		$expression = new Expr\BinaryOp\Equal($value, $var);

		$canonicalized = Comparison::canonicalize($expression);

		$this->assertSame($canonicalized->left, $var);
		$this->assertSame($canonicalized->right, $value);
	}

	public function inequalityRelationProvider() {
		return array(
			array('PhpParser\Node\Expr\BinaryOp\Smaller', 'Expr_BinaryOp_Greater'),
			array('PhpParser\Node\Expr\BinaryOp\Greater', 'Expr_BinaryOp_Smaller'),
			array('PhpParser\Node\Expr\BinaryOp\SmallerOrEqual', 'Expr_BinaryOp_GreaterOrEqual'),
			array('PhpParser\Node\Expr\BinaryOp\GreaterOrEqual', 'Expr_BinaryOp_SmallerOrEqual'),
		);
	}

	/**
	 * @dataProvider inequalityRelationProvider
	 * @test
	 */
	public function canonicalizeCorrectlyChangesRelationTypeForInequalityRelations($initialRelationClass,
	                                                                               $canonicalizedType) {
		$var = $this->mockVariable('foo');
		$value = $this->mockInteger(5);
		$expression = new $initialRelationClass($value, $var);

		$canonicalized = Comparison::canonicalize($expression);

		$this->assertEquals($canonicalizedType, $canonicalized->getType());
	}


	protected function mockVariable($variableName) {
		return new Expr\Variable($variableName);
	}

	protected function mockInteger($value) {
		return new Scalar\LNumber($value);
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\DynamicAnalysis\Data;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\EvalValueFetcher;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;


class EvalValueFetcherTest extends UnitTestCase {

	public function notFetchableExpressionsProvider() {
		return array(
			'simple variable' => array(
				new Expr\Variable('foo')
			),
			'local object property' => array(
				new Expr\PropertyFetch(new Expr\Variable('this'), 'foo'),
			),
			'different object property' => array(
				new Expr\PropertyFetch(new Expr\Variable('foo'), 'bar'),
			),
		);
	}

	/**
	 * @param Expr $expression
	 *
	 * @test
	 * @dataProvider notFetchableExpressionsProvider
	 */
	public function canFetchReturnsFalseForNotFetchableExpressions(Expr $expression) {
		$subject = new EvalValueFetcher($this->getMock('AndreasWolf\DebuggerClient\Session\DebugSession'));

		$this->assertFalse($subject->canFetch($expression));
	}

	public function fetchableExpressionsProvider() {
		return array(
			'local object method call' => array(
				new Expr\MethodCall(new Expr\Variable('this'), 'foo'),
			),
			'local object method call with property fetch on result' => array(
				new Expr\PropertyFetch(new Expr\MethodCall(new Expr\Variable('this'), 'foo'), 'bar'),
			),
		);
	}

	/**
	 * @param Expr $expression
	 *
	 * @test
	 * @dataProvider fetchableExpressionsProvider
	 */
	public function canFetchReturnsTrueForFetchableExpressions(Expr $expression) {
		$subject = new EvalValueFetcher($this->getMock('AndreasWolf\DebuggerClient\Session\DebugSession'));

		$this->assertTrue($subject->canFetch($expression));
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\DynamicAnalysis\Data;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\PropertyValueFetcher;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;


class PropertyValueFetcherTest extends UnitTestCase {

	public function fetchableExpressionsProvider() {
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
	 * @dataProvider fetchableExpressionsProvider
	 */
	public function canFetchReturnsTrueForFetchableExpressions(Expr $expression) {
		$subject = new PropertyValueFetcher($this->getDebugSession());

		$this->assertTrue($subject->canFetch($expression));
	}

	public function notFetchableExpressionsProvider() {
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
	 * @dataProvider notFetchableExpressionsProvider
	 */
	public function canFetchReturnsFalseForNotFetchableExpressions(Expr $expression) {
		$subject = new PropertyValueFetcher($this->getDebugSession());

		$this->assertFalse($subject->canFetch($expression));
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getDebugSession() {
		return $this->getMockBuilder('AndreasWolf\DebuggerClient\Session\DebugSession')
			->disableOriginalConstructor()->getMock();
	}

}

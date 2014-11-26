<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Service;

use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use AndreasWolf\DecisionCoverage\Tests\ParserBasedTestCase;


class ExpressionServiceTest extends ParserBasedTestCase {

	public function compoundExpressionProvider() {
		return array(
			'boolean AND' => array(
				'$a == TRUE && $b == FALSE;',
			),
			'boolean OR' => array(
				'$a == TRUE || $b == FALSE;',
			),
			'logical AND' => array(
				'$a == TRUE AND $b == FALSE;',
			),
			'logical OR' => array(
				'$a == TRUE OR $b == FALSE;',
			),
			'logical XOR' => array(
				'$a == TRUE XOR $b == FALSE;',
			),
		);
	}

	/**
	 * @param string $code
	 * @test
	 * @dataProvider compoundExpressionProvider
	 */
	public function compoundBooleanExpressionIsCorrectlyClassifiedAsDecision($code) {
		$nodes = $this->parseCode($code);
		$subject = new ExpressionService();

		$this->assertTrue($subject->isDecisionExpression($nodes[0]));
	}

	/**
	 * @test
	 */
	public function equalityCheckIsNotClassifiedAsDecision() {
		$nodes = $this->parseCode('$foo == "bar";');
		$subject = new ExpressionService();

		$this->assertFalse($subject->isDecisionExpression($nodes[0]));
	}

}

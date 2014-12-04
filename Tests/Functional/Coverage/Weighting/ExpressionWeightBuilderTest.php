<?php
namespace AndreasWolf\DecisionCoverage\Tests\Functional\Coverage\Weighting;

use AndreasWolf\DebuggerClient\Tests\Functional\FunctionalTestCase;
use AndreasWolf\DecisionCoverage\Coverage\Weighting\ExpressionWeightBuilder;
use AndreasWolf\DecisionCoverage\Tests\ParserTestIntegration;


class ExpressionWeightBuilderTest extends FunctionalTestCase {
	use ParserTestIntegration;


	public function complexExpressionDataProvider() {
		return array(
			'single boolean AND' => array(
				'$A && $B;',
				1, 2
			),
			'single boolean OR' => array(
				'$A || $B;',
				2, 1
			),
			'three-part boolean AND' => array(
				'$A && $B && $C;',
				1, 3
			),
			'three-part boolean OR' => array(
				'$A || $B || $C;',
				3, 1
			),
			'AND with nested OR' => array(
				'($A || $B) && ($C || $D);',
				4, 3
			)
		);
	}

	/**
	 * @test
	 * @dataProvider complexExpressionDataProvider
	 */
	public function weightForComplexDecisionsIsCorrectlyBuilt($expression, $expectedTrueWeight, $expectedFalseWeight) {
		$subject = new ExpressionWeightBuilder();
		$nodes = $this->parseCode($expression);

		$subject->buildForExpression($nodes[0]);
		$decisionWeight = $nodes[0]->getAttribute('coverage__weight');

		$this->assertEquals($expectedTrueWeight, $decisionWeight->getTrueValue());
		$this->assertEquals($expectedFalseWeight, $decisionWeight->getFalseValue());

	}
	
}

<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\Comparison;
use AndreasWolf\DecisionCoverage\Coverage\TestVariable;
use AndreasWolf\DecisionCoverage\Tests\Unit\ParserBasedTestCase;
use PhpParser\Lexer;
use PhpParser\Parser;


/**
 * Test case for the abstraction of a variable’s comparisons in e.g. one branch of an if-statement
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TestVariableTest extends ParserBasedTestCase {

	public function simpleComparisonDataProvider() {
		return array(
			'equal string' => array(
				'code' => '$variable == "bar";',
				'expected tested value' => array(array(Comparison::EQUAL, 'bar')),
			),
			'equal string, yoda notation' => array(
				'code' => '"bar" == $variable;',
				'expected tested value' => array(array(Comparison::EQUAL, 'bar')),
			),
			'unequal string' => array(
				'code' => '$variable != "bar";',
				'expected tested value' => array(array(Comparison::NOT_EQUAL, 'bar')),
			),
			'identical string' => array(
				'code' => '$variable === "bar";',
				'expected tested value' => array(array(Comparison::IDENTICAL, 'bar')),
			),
			'smaller number' => array(
				'code' => '$variable < 10;',
				'expected tested value' => array(array(Comparison::SMALLER, 10)),
			),
			'smaller or equal number' => array(
				'code' => '$variable <= 10;',
				'expected tested value' => array(array(Comparison::SMALLER_OR_EQUAL, 10)),
			),
			'greater number' => array(
				'code' => '$variable > 10;',
				'expected tested value' => array(array(Comparison::GREATER, 10)),
			),
			'greater or equal number' => array(
				'code' => '$variable >= 10;',
				'expected tested value' => array(array(Comparison::GREATER_OR_EQUAL, 10)),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider simpleComparisonDataProvider
	 */
	public function simpleComparisonsAreProcessedCorrectly($code, $expectedTestValues) {
		$parsedNodes = $this->parseCode($code);

		$testVariable = new TestVariable($parsedNodes[0]);

		$this->assertEquals($expectedTestValues, $testVariable->getTestedValues());
	}

	/**
	 * @test
	 */
	public function inequalityComparisonInYodaNotationIsCorrectlyInverted() {
		$parsedNodes = $this->parseCode('10 < $variable;');

		$testVariable = new TestVariable($parsedNodes[0]);

		$this->assertEquals(array(array(Comparison::GREATER, 10)), $testVariable->getTestedValues());
	}

	/**
	 * @test
	 */
	public function twoComparisonsForOneVariableAreProcessedCorrectly() {
		$parsedNodes = $this->parseCode('$variable > 10 && $variable < 20;');

		$testVariable = new TestVariable($parsedNodes[0]->left);
		$testVariable->addValueTest($parsedNodes[0]->right);

		$this->assertEquals(
			array(array(Comparison::GREATER, 10), array(Comparison::SMALLER, 20)),
			$testVariable->getTestedValues()
		);
	}

}
 
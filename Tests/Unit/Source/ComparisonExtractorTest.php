<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Source;

use AndreasWolf\DecisionCoverage\Source\ComparisonExtractor;
use AndreasWolf\DecisionCoverage\Tests\ParserBasedTestCase;


/**
 * Test case for the comparison extractor class.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ComparisonExtractorTest extends ParserBasedTestCase {

	/**
	 * @param $code
	 * @return \PhpParser\Node\Expr[]
	 */
	protected function extractComparisonsFromIfStatementCode($code) {
		$parsedNode = $this->parseCode($code);

		$comparisonExtractor = new ComparisonExtractor();
		$comparisons = $comparisonExtractor->extractFromIf($parsedNode[0]);

		return $comparisons;
	}

	public function singleConditionDataProvider() {
		return array(
			'string equal' => array(
				'code' => 'if ($foo == "bar") {}'
			),
			'string unequal' => array(
				'code' => 'if ($foo != "bar") {}'
			),
			'string identical' => array(
				'code' => 'if ($foo === "bar") {}'
			),
			'integer greater' => array(
				'code' => 'if ($foo > 10) {}'
			),
			'integer greater or equal' => array(
				'code' => 'if ($foo >= 10) {}'
			),
			'integer smaller' => array(
				'code' => 'if ($foo < 10) {}'
			),
			'integer smaller or equal' => array(
				'code' => 'if ($foo <= 10) {}'
			),
			'integer equal' => array(
				'code' => 'if ($foo == 10) {}'
			),
			'integer identical' => array(
				'code' => 'if ($foo === 10) {}'
			)
		);
	}

	/**
	 * This test also covers that all possible comparison operators are supported (though not for every data type)
	 *
	 * @test
	 * @dataProvider singleConditionDataProvider
	 */
	public function singleConditionInIfStatementIsCorrectlyExtract($code) {
		$comparisons = $this->extractComparisonsFromIfStatementCode($code);

		$this->assertCount(1, $comparisons);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[0]);
	}

	/**
	 * @test
	 */
	public function twoConditionsConnectedWithAndAreCorrectlyExtracted() {
		$comparisons = $this->extractComparisonsFromIfStatementCode('if ($foo == "bar" && $baz < 10) {}');

		$this->assertCount(2, $comparisons);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[0]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[1]);
	}

	/**
	 * @test
	 */
	public function threeOrMoreConditionsConnectedWithAndAreCorrectlyExtracted() {
		$comparisons = $this->extractComparisonsFromIfStatementCode('if ($foo == "bar" && $baz < 10 && $baz > 0) {}');

		$this->assertCount(3, $comparisons);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[0]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[1]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[2]);
	}

	/**
	 * @test
	 */
	public function wordsAsBooleanOperatorsAreCorrectlyExtracted() {
		$comparisons = $this->extractComparisonsFromIfStatementCode('if ($foo == "bar" and $baz < 10 or $baz > 0 xor $bar == 10) {}');

		$this->assertCount(4, $comparisons);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[0]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[1]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[2]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[3]);
	}

	/**
	 * @test
	 */
	public function threeConditionsConnectedWithOrAndAndAreCorrectlyExtracted() {
		$comparisons = $this->extractComparisonsFromIfStatementCode('if ($foo == "bar" && $baz < 10 || $baz > 20) {}');

		$this->assertCount(3, $comparisons);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[0]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[1]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[2]);
	}

	/**
	 * @test
	 */
	public function threeConditionsConnectedWithAndAndOrAndSurroundedByParenthesesAreCorrectlyExtracted() {
		$comparisons = $this->extractComparisonsFromIfStatementCode('if ($foo == "bar" && ($baz < 10 || $baz > 20)) {}');

		$this->assertCount(3, $comparisons);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[0]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[1]);
		$this->assertInstanceOf('PhpParser\Node\Expr\BinaryOp', $comparisons[2]);
	}

	public function equalityComparisonDataProvider() {
		return array(
			'equal string' => array(
				'code' => 'if (!($foo == "bar")) {}',
				'expectedClass' => 'PhpParser\Node\Expr\BinaryOp\NotEqual',
			),
			'unequal string' => array(
				'code' => 'if (!($foo != "bar")) {}',
				'expectedClass' => 'PhpParser\Node\Expr\BinaryOp\Equal',
			),
			'identical string' => array(
				'code' => 'if (!($foo === "bar")) {}',
				'expectedClass' => 'PhpParser\Node\Expr\BinaryOp\NotIdentical',
			),
			'not identical string' => array(
				'code' => 'if (!($foo !== "bar")) {}',
				'expectedClass' => 'PhpParser\Node\Expr\BinaryOp\Identical',
			)
		);
	}

	/**
	 * @test
	 * @dataProvider equalityComparisonDataProvider
	 */
	public function invertedEqualityComparisonIsCorrectlyReturned($code, $expectedClass) {
		$comparisons = $this->extractComparisonsFromIfStatementCode($code);

		$this->assertCount(1, $comparisons);
		$this->assertInstanceOf($expectedClass, $comparisons[0]);
	}

	public function inequalityComparisonDataProvider() {
		return array(
			'greater' => array(
				'code' => 'if (!($foo > 10)) {}',
				'expectedClass' => 'PhpParser\Node\Expr\BinaryOp\SmallerOrEqual',
			),
			'greater or equal' => array(
				'code' => 'if (!($foo >= 10)) {}',
				'expectedClass' => 'PhpParser\Node\Expr\BinaryOp\Smaller',
			),
			'smaller' => array(
				'code' => 'if (!($foo < 10)) {}',
				'expectedClass' => 'PhpParser\Node\Expr\BinaryOp\GreaterOrEqual',
			),
			'smaller or equal' => array(
				'code' => 'if (!($foo <= 10)) {}',
				'expectedClass' => 'PhpParser\Node\Expr\BinaryOp\Greater',
			)
		);
	}

	/**
	 * @test
	 * @dataProvider inequalityComparisonDataProvider
	 */
	public function invertedInequalityComparisonIsCorrectlyReturned($code, $expectedClass) {
		$comparisons = $this->extractComparisonsFromIfStatementCode($code);

		$this->assertCount(1, $comparisons);
		$this->assertInstanceOf($expectedClass, $comparisons[0]);
	}

}

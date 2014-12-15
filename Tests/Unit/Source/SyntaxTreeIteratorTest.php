<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Source;

use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\Tests\ParserBasedTestCase;


class SyntaxTreeIteratorTest extends ParserBasedTestCase {

	public function ifConditionalTypesProvider() {
		return array(
			'single variable' => array(
				'if ($foo) {}',
				'PhpParser\Node\Expr\Variable'
			),
			'equality comparison' => array(
				'if ($foo == "bar") {}',
				'PhpParser\Node\Expr\BinaryOp\Equal'
			)
		);
	}

	/**
	 * @test
	 * @dataProvider ifConditionalTypesProvider
	 */
	public function variousTypesOfConditionalsOfIfAreCorrectlyIterated($code, $assertedChildType) {
		$nodes = $this->parseCode($code);

		$subject = new SyntaxTreeIterator($nodes, TRUE);

		$ifChildren = $subject->getChildren();
		$this->assertCount(1, $ifChildren);
		$this->assertInstanceOf($assertedChildType, $ifChildren->current());
	}

	/**
	 * @test
	 */
	public function childrenOfNamespaceStatementAreCorrectlyDetected() {
		$nodes = $this->parseCode('namespace MyNamespace;
		class Foo {}');

		$subject = new SyntaxTreeIterator($nodes, TRUE);

		$this->assertTrue($subject->hasChildren());
	}

	/**
	 * @test
	 */
	public function childrenOfClassStatementAreCorrectlyDetected() {
		$nodes = $this->parseCode('class Foo {
			public function bar() {}
		}');

		$subject = new SyntaxTreeIterator($nodes, TRUE);

		$this->assertTrue($subject->hasChildren());
	}

	/**
	 * @test
	 */
	public function statementsArrayOfEmptyIfStatementIsNotIncludedWhenIncludingAllSubnodes() {
		$nodes = $this->parseCode('if ($foo == "bar") {}');

		$subject = new SyntaxTreeIterator($nodes, TRUE);

		$ifChildren = $subject->getChildren();
		$this->assertInstanceOf('PhpParser\Node\Stmt\If_', $subject->current());
		$this->assertCount(1, $ifChildren);
	}

	public function recursiveIterationDataProvider() {
		$variableSmallerThanNumber = array('Expr_BinaryOp_Smaller', 'Expr_Variable', 'Scalar_LNumber');
		$variableEqualsString = array('Expr_BinaryOp_Equal', 'Expr_Variable', 'Scalar_String');
		$ifStatementWithSingleCondition = array_merge(array('Stmt_If'), $variableEqualsString);

		return array(
			'if statement with single condition and empty body' => array(
				'if ($foo == "bar") {}',
				4,
				$ifStatementWithSingleCondition,
			),
			'if statement with single condition and echo in body' => array(
				'if ($foo == "bar") { echo "baz"; }',
				6, // If + 3 for condition + 2 for echo
				array_merge(
					$ifStatementWithSingleCondition,
					array('Stmt_Echo', 'Scalar_String')
				),
			),
			'if statement with decision' => array(
				'if ($foo == "bar" && $baz < 10) {}',
				8, // If, And, 3 each for both conditions
				array_merge(
					array('Stmt_If', 'Expr_BinaryOp_BooleanAnd'),
					$variableEqualsString, $variableSmallerThanNumber
				)
			),
			'object property access' => array(
				'$this->foo;',
				2,
				array('Expr_PropertyFetch', 'Expr_Variable')
			),
			'object method call' => array(
				'$this->foo();',
				2,
				array('Expr_MethodCall', 'Expr_Variable')
			),
			'property access on method return value' => array(
				'$this->foo()->bar;',
				3,
				array('Expr_PropertyFetch', 'Expr_MethodCall', 'Expr_Variable')
			),
		);
	}

	/**
	 * @test
	 * @dataProvider recursiveIterationDataProvider
	 */
	public function syntaxTreeCanBeRecursivelyIterated($code, $expectedNodeCount, $expectedTypes) {
		$nodes = $this->parseCode($code);

		$subject = new \RecursiveIteratorIterator(
			new SyntaxTreeIterator($nodes, TRUE), \RecursiveIteratorIterator::SELF_FIRST
		);

		$currentNode = 0;
		foreach ($subject as $currentItem) {
			++$currentNode;

			if (!isset($expectedTypes[$currentNode - 1])) {
				continue;
			}
			$expectedType = $expectedTypes[$currentNode - 1];
			try {
				$this->assertEquals($expectedType, $currentItem->getType());
			} catch (\PHPUnit_Framework_ExpectationFailedException $e) {
				throw new \PHPUnit_Framework_ExpectationFailedException("Expectation failed for iterator item "
					. ($currentNode + 1) . " (" . var_dump($currentItem) . ")",
					$e->getComparisonFailure(), $e);
			}
		}
		$this->assertEquals($expectedNodeCount, $currentNode);
		$this->assertFalse($subject->valid());
	}
	
}

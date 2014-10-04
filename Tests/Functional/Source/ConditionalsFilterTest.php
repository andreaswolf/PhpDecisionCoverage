<?php
namespace AndreasWolf\DecisionCoverage\Tests\Functional\Source;
use AndreasWolf\DecisionCoverage\Source\ConditionalsFilter;
use AndreasWolf\DecisionCoverage\Source\DecisionStatement;
use AndreasWolf\DecisionCoverage\Source\StatementContext;
use AndreasWolf\DecisionCoverage\Tests\SourceTestCase;
use PhpParser\Lexer;
use PhpParser\Parser;


/**
 * Test case for the conditionals filter.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ConditionalsFilterTest extends SourceTestCase {

	protected function walkTree($code) {
		$sourceFile = $this->createSourceFileForCode($code);

		$filter = new ConditionalsFilter();

		return $filter->walkSourceFile($sourceFile);
	}

	/**
	 * @test
	 */
	public function ifStatementInClassMethodIsReturned() {
		$code = 'class Foo {
	public function bar($paramA) {
		if ($paramA > 10) {
			echo "param A is greater than 10";
		}
	}
}';
		$conditionals = $this->walkTree($code);

		$this->assertEquals(1, count($conditionals));
		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\Source\ConditionalStatement', $conditionals[0]);
	}

	/**
	 * @test
	 */
	public function statementContextContainsFilePath() {
		$code = 'class Foo {
	public function bar($paramA) {
		if ($paramA > 10) {
			echo "param A is greater than 10";
		}
	}
}';
		/** @var DecisionStatement $conditionals */
		$conditionals = $this->walkTree($code);

		/** @var StatementContext $context */
		$context = $conditionals[0]->getContext();
		// file path is a temporary path, so just check if its not empty. TODO This should be improved
		$this->assertNotEmpty($context->getFilePath());
	}

	/**
	 * @test
	 */
	public function statementContextContainsClassName() {
		$code = 'class Foo {
	public function bar($paramA) {
		if ($paramA > 10) {
			echo "param A is greater than 10";
		}
	}
}';
		/** @var DecisionStatement $conditionals */
		$conditionals = $this->walkTree($code);

		/** @var StatementContext $context */
		$context = $conditionals[0]->getContext();
		$this->assertEquals('Foo', $context->getClassName());
	}

	/**
	 * @test
	 */
	public function statementContextContainsFunctionName() {
		$code = 'class Foo {
	public function bar($paramA) {
		if ($paramA > 10) {
			echo "param A is greater than 10";
		}
	}
}';
		/** @var DecisionStatement $conditionals */
		$conditionals = $this->walkTree($code);

		/** @var StatementContext $context */
		$context = $conditionals[0]->getContext();
		$this->assertEquals('bar', $context->getFunctionName());
	}

	/**
	 * @test
	 */
	public function statementReturnsCorrectLine() {
		$code = 'class Foo {
	public function bar($paramA) {
		if ($paramA > 10) {
			echo "param A is greater than 10";
		}
	}
}';
		/** @var DecisionStatement $conditionals */
		$conditionals = $this->walkTree($code);

		$this->assertEquals(3, $conditionals[0]->getLine());
	}

}

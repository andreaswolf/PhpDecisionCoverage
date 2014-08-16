<?php
namespace AndreasWolf\DecisionCoverage\Tests\Functional\Source;
use AndreasWolf\DecisionCoverage\Source\SourceFile;
use AndreasWolf\DecisionCoverage\Tests\SourceTestCase;
use PhpParser\Lexer;
use PhpParser\Parser;


/**
 * Test case for the source file class.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SourceFileTest extends SourceTestCase {

	/**
	 * @test
	 */
	public function statementsContainParsedClass() {
		$code = 'class Foo {
	public function bar() {
		echo "hier";
	}
}';
		$file = $this->createSourceFileForCode($code);

		$nodes = $file->getTopLevelStatements();
		$this->assertCount(1, $nodes);
		$this->assertInstanceOf('PhpParser\\Node\\Stmt\\Class_', $nodes[0]);
	}

	/**
	 * @test
	 */
	public function statementsContainNamespaceAndClass() {
		$code = 'namespace Foo;
class Bar {
	public function baz() {
		echo "hier";
	}
}';
		$file = $this->createSourceFileForCode($code);

		$nodes = $file->getTopLevelStatements();
		$this->assertCount(1, $nodes);
		$this->assertInstanceOf('PhpParser\\Node\\Stmt\\Namespace_', $nodes[0]);
		$this->assertInstanceOf('PhpParser\\Node\\Stmt\\Class_', $nodes[0]->stmts[0]);
	}

}

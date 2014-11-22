<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis\SyntaxTree;

use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\SyntaxTree;
use AndreasWolf\DecisionCoverage\Tests\ParserBasedTestCase;


class SyntaxTreeTest extends ParserBasedTestCase {

	protected function createSubject() {
		return new SyntaxTree($this->parseCode('function foo() {
			echo "Test";
		}'));
	}

	/**
	 * @test
	 */
	public function getIteratorReturnsRecursiveIteratorInstance() {
		$subject = $this->createSubject();

		$this->assertInstanceOf('RecursiveIteratorIterator', $subject->getIterator());
	}

	/**
	 * @test
	 */
	public function subIteratorOfReturnedIteratorIsASyntaxTreeIterator() {
		$subject = $this->createSubject();

		$this->assertInstanceOf('AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator', $subject->getIterator()->getSubIterator());
	}

}

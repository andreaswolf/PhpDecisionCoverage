<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Report\Html;

use AndreasWolf\DecisionCoverage\Report\Html\SourceFileTokenizer;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class SourceFileTokenizerTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function tokenizeReturnsAllLinesFromFile() {
		$subject = new SourceFileTokenizer();

		$lines = $subject->getSourceLinesInFile(__DIR__ . '/Fixtures/SimpleTestFile.php');

		$this->assertCount(2, $lines);
		$this->assertEquals('foo', $lines[0]);
		$this->assertEquals('bar', $lines[1]);
	}

	/**
	 * @test
	 */
	public function windowsLineEndingsAreCorrectlyStripped() {
		$subject = new SourceFileTokenizer();

		$lines = $subject->getSourceLinesInFile(__DIR__ . '/Fixtures/WindowsLineEndings.php');

		$this->assertCount(3, $lines);
		$this->assertEquals('This file', $lines[0]);
		$this->assertEquals('has Windows', $lines[1]);
		$this->assertEquals('line endings', $lines[2]);
	}

}

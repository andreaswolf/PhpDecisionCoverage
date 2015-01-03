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

		$result = $subject->getSourceLinesInFile(__DIR__ . '/Fixtures/SimpleTestFile.php');

		$this->assertEquals(2, $result->countLines());
		$this->assertEquals('foo', $result->getLine(1));
		$this->assertEquals('bar', $result->getLine(2));
	}

	/**
	 * @test
	 */
	public function windowsLineEndingsAreCorrectlyStripped() {
		$subject = new SourceFileTokenizer();

		$result = $subject->getSourceLinesInFile(__DIR__ . '/Fixtures/WindowsLineEndings.php');

		$this->assertEquals(3, $result->countLines());
		$this->assertEquals('This file', $result->getLine(1));
		$this->assertEquals('has Windows', $result->getLine(2));
		$this->assertEquals('line endings', $result->getLine(3));
	}

	/**
	 * @test
	 */
	public function offsetsForFileWithUnixLineEndingsAreCorrectlyRetrieved() {
		$subject = new SourceFileTokenizer();

		$result = $subject->getSourceLinesInFile(__DIR__ . '/Fixtures/SimpleTestFile.php');

		$this->assertEquals(0, $result->getLineOffset(1));
		$this->assertEquals(4, $result->getLineOffset(2));
	}

	/**
	 * @test
	 */
	public function offsetsForFileWithWindowsLineEndingsAreCorrectlyRetrieved() {
		$subject = new SourceFileTokenizer();

		$result = $subject->getSourceLinesInFile(__DIR__ . '/Fixtures/WindowsLineEndings.php');

		// "This file\r\n" = 11
		// "has Windows\r\n" = 13
		// "line endings" = 12
		$this->assertEquals(0, $result->getLineOffset(1));
		$this->assertEquals(11, $result->getLineOffset(2));
		$this->assertEquals(24, $result->getLineOffset(3));
	}

}

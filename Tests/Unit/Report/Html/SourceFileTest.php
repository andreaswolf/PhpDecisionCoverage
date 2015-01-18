<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Report\Html;

use AndreasWolf\DecisionCoverage\Report\Html\SourceFile;
use AndreasWolf\DecisionCoverage\Report\Html\SourceFileTokenizer;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class SourceFileTest extends UnitTestCase {


	protected function tokenizeFile($filePath) {
		$tokenizer = new SourceFileTokenizer();
		return $tokenizer->getSourceLinesInFile($filePath);
	}

	/**
	 * @test
	 */
	public function lineCountInFileIsCorrect() {
		$tokens = $this->tokenizeFile(__DIR__ . '/Fixtures/SimpleTextFile.php');
		$subject = SourceFile::createFromTokenizationResult($tokens);

		$this->assertCount(2, $subject->getLines());
	}

	/**
	 * @test
	 */
	public function linesCanBeFetchedByFirstCharacterOffset() {
		$tokens = $this->tokenizeFile(__DIR__ . '/Fixtures/SimpleTextFile.php');
		$subject = SourceFile::createFromTokenizationResult($tokens);

		$this->assertEquals($subject->getLine(1), $subject->getLineByCharacterOffset(0));
		$this->assertEquals($subject->getLine(2), $subject->getLineByCharacterOffset(4));
	}

	/**
	 * @test
	 */
	public function linesCanBeFetchedByCharacterOffsetWithinLine() {
		$tokens = $this->tokenizeFile(__DIR__ . '/Fixtures/SimpleTextFile.php');
		$subject = SourceFile::createFromTokenizationResult($tokens);

		$this->assertEquals($subject->getLine(1), $subject->getLineByCharacterOffset(2));
		$this->assertEquals($subject->getLine(2), $subject->getLineByCharacterOffset(6));
	}

}

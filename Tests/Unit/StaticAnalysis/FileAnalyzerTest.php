<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileAnalyzer;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use VirtualFileSystem\FileSystem;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class FileAnalyzerTest extends UnitTestCase {

	protected function setUp() {
		$l = new \VirtualFileSystem\Loader();
		$l->register();
	}

	/**
	 * @test
	 */
	public function analysisResultsCanBeWrittenToFile() {
		$serializedData = 'O:8:"stdClass":0:{}';
		$resultSet = $this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet')
			->disableOriginalConstructor()->getMock();
		$resultSet->expects($this->once())->method('serialize')->willReturn($serializedData);

		$subject = new FileAnalyzer();
		$fs = new FileSystem();

		$subject->writeAnalysisResultsToFile($fs->path('/results.txt'), $resultSet);
		$this->assertStringEqualsFile($fs->path('/results.txt'), $serializedData);
	}

}

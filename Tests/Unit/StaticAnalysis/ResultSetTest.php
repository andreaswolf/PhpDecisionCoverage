<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class ResultSetTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function fileResultCanBeAddedAndRetrievedByPath() {
		$path = '/my/file/path';
		$fileResult = $this->mockFileResult($path);

		$resultSet = new ResultSet();

		$resultSet->addFileResult($fileResult);

		$this->assertSame($fileResult, $resultSet->getResultForPath($path));
	}

	/**
	 * @test
	 */
	public function allAddedResultsCanBeRetrieved() {
		$fileResultA = $this->mockFileResult('/my/file/path');
		$fileResultB = $this->mockFileResult('/my/other/path');
		$resultSet = new ResultSet();

		$resultSet->addFileResult($fileResultA);
		$resultSet->addFileResult($fileResultB);

		$this->assertEquals(array(
			'/my/file/path' => $fileResultA,
			'/my/other/path' => $fileResultB,
		), $resultSet->getFileResults());
	}

	/**
	 * @test
	 */
	public function serializedResultSetContainsSameInformation() {
		$fileResultA = $this->mockFileResult('/my/file/path');
		$fileResultB = $this->mockFileResult('/my/other/path');
		$resultSet = new ResultSet();

		$resultSet->addFileResult($fileResultA);
		$resultSet->addFileResult($fileResultB);

		$unserializedSet = unserialize($resultSet->serialize());

		$this->assertEquals($resultSet, $unserializedSet);
	}

	/**
	 * @param string $filePath
	 * @return FileResult
	 */
	protected function mockFileResult($filePath) {
		/** @var FileResult $fileResult */
		$fileResult = $this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult')
			->disableOriginalConstructor()->getMock();
		$fileResult->expects($this->once())->method('getFilePath')->will($this->returnValue($filePath));

		return $fileResult;
	}

}

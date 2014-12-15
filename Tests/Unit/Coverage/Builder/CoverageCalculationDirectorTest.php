<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageCalculationDirector;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\SyntaxTree;
use AndreasWolf\DecisionCoverage\Tests\ParserBasedTestCase;


class CoverageCalculationDirectorTest extends ParserBasedTestCase {
	use CoverageBuilderTestTrait;

	/**
	 * @test
	 */
	public function builderFactoryIsAskedToCreateCoverageBuilderForDecision() {
		$nodes = $this->parseCode('if ($foo == "bar" && $baz < 10) {}');
		$analysisResult = $this->mockAnalysisResultSet($nodes);

		$mockedBuilderFactory = $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageBuilderFactory')
			->disableOriginalConstructor()->getMock();
		$mockedBuilderFactory->expects($this->once())->method('createBuilderForExpression')
			->with($this->isInstanceOf('PhpParser\Node\Expr\BinaryOp\BooleanAnd'));
		$subject = new CoverageCalculationDirector(new CoverageSet($this->mockCoverageDataSet($analysisResult)),
			$mockedBuilderFactory);

		$subject->build();
	}


	protected function mockAnalysisResultSet($syntaxTreeNodes) {
		$resultSet = new ResultSet();
		$resultSet->addFileResult(new FileResult('-', new SyntaxTree($syntaxTreeNodes)));

		return $resultSet;
	}

	protected function mockCoverageDataSet(ResultSet $analysisResult) {
		return new CoverageDataSet($analysisResult);
	}

	/**
	 * @return CoverageDataSet
	 */
	protected function getDummyDataSet() {
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet')
			->disableOriginalConstructor()->getMock();
	}

}
 
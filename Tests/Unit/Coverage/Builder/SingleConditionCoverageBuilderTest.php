<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Builder;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\Builder\SingleConditionCoverageBuilder;
use AndreasWolf\DecisionCoverage\Coverage\Event\SampleEvent;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;


class SingleConditionCoverageBuilderTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function dataSampleHandlerExtractsAddsValueToCoverage(){
		$expression = $this->mockExpression(1);
		$mockedCoverage = $this->mockCoverage();
		$mockedCoverage->expects($this->once())->method('recordCoveredValue')
			->with($this->isInstanceOf('AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue'));
		$dataSample = new DataSample($this->mockProbe());
		$dataSample->addValue($expression, new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, TRUE));

		$subject = new SingleConditionCoverageBuilder($expression, $mockedCoverage, $this->mockEventDispatcher());
		$subject->dataSampleReceivedHandler(new SampleEvent($dataSample));
	}

	/**
	 * @test
	 */
	public function eventIsTriggeredWhenValueIsCovered() {
		$expression = $this->mockExpression(1);
		$mockedCoverage = $this->mockCoverage();
		$dataSample = $this->mockDataSample($expression, ExpressionValue::TYPE_BOOLEAN, TRUE);
		$eventDispatcher = $this->mockEventDispatcher();
		$eventDispatcher->expects($this->once())->method('dispatch')->with($this->equalTo('coverage.builder.part.covered'));

		$subject = new SingleConditionCoverageBuilder($expression, $mockedCoverage, $eventDispatcher);
		$subject->dataSampleReceivedHandler(new SampleEvent($dataSample));
	}

	/**
	 * @test
	 */
	public function dataSampleHandlerDoesNotAddValueIfNoValuePresentForExpression() {
		$expression = $this->mockExpression(1);
		$anotherExpression = $this->mockExpression(2);

		$mockedCoverage = $this->mockCoverage();
		$mockedCoverage->expects($this->never())->method('recordCoveredValue');
		$dataSample = new DataSample($this->mockProbe());
		$dataSample->addValue($anotherExpression, new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, TRUE));

		$subject = new SingleConditionCoverageBuilder($expression, $mockedCoverage, $this->mockEventDispatcher());
		$subject->dataSampleReceivedHandler(new SampleEvent($dataSample));
	}

	/**
	 * @test
	 */
	public function eventIsTriggeredWhenValueIsNotPresent() {
		$expression = $this->mockExpression(1);
		$anotherExpression = $this->mockExpression(2);

		$mockedCoverage = $this->mockCoverage();
		$dataSample = $this->mockDataSample($anotherExpression, ExpressionValue::TYPE_BOOLEAN, TRUE);
		$eventDispatcher = $this->mockEventDispatcher();
		$eventDispatcher->expects($this->never())->method('dispatch');

		$subject = new SingleConditionCoverageBuilder($expression, $mockedCoverage, $eventDispatcher);
		$subject->dataSampleReceivedHandler(new SampleEvent($dataSample));
	}


	protected function mockEventDispatcher() {
		return $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
	}

	protected function mockProbe() {
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\DataCollectionProbe')
			->disableOriginalConstructor()->getMock();
	}

	protected function mockCoverage() {
		return $this->getMockBuilder('AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage')
			->disableOriginalConstructor()->getMock();
	}

	protected function mockExpression($nodeId) {
		$mock = $this->getMockBuilder('PhpParser\Node\Expr')->disableOriginalConstructor()->getMock();
		$mock->expects($this->any())->method('getAttribute')->with('coverage__nodeId')->willReturn($nodeId);

		return $mock;
	}

	/**
	 * @param $expression
	 * @param $dataType
	 * @param $value
	 * @return DataSample
	 */
	protected function mockDataSample($expression, $dataType, $value) {
		$dataSample = new DataSample($this->mockProbe());
		$dataSample->addValue($expression, new ExpressionValue($dataType, $value));

		return $dataSample;
	}

}

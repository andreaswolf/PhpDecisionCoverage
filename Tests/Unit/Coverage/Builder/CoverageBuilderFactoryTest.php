<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageBuilderFactory;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Equal;


class CoverageBuilderFactoryTest extends UnitTestCase {
	use CoverageBuilderTestTrait;

	/**
	 * @test
	 */
	public function createdConditionBuilderIsAddedToEventHandler() {
		$mockedEventDispatcher = $this->mockEventDispatcher();
		$mockedEventDispatcher->expects($this->once())->method('addSubscriber')
			->with($this->isInstanceOf('AndreasWolf\DecisionCoverage\Coverage\Builder\SingleConditionCoverageBuilder'));

		$subject = new CoverageBuilderFactory($mockedEventDispatcher, $this->mockCoverageFactory());

		$subject->createBuilderForCondition($this->getMock('PhpParser\Node\Expr'));
	}

	/**
	 * @test
	 */
	public function creatingBuilderForConditionUsesCoverageFactoryToCreateCoverage() {
		$expression = $this->getMock('PhpParser\Node\Expr');
		$coverageFactory = $this->mockCoverageFactory();
		$coverageFactory->expects($this->once())->method('createCoverageForNode')->with($this->equalTo($expression))
		->will($this->returnValue($this->mockCoverage()));

		$subject = new CoverageBuilderFactory($this->mockEventDispatcher(), $coverageFactory);

		$subject->createBuilderForCondition($expression);
	}

	/**
	 * @test
	 */
	public function creatingBuilderForDecisionCreatesCoveragesForConditionsAndThenBuilderForDecision() {
		$equal = $this->mockExpressionNode('BinaryOp\\Equal');
		$smaller = $this->mockExpressionNode('BinaryOp\\Smaller');
		$expression = new BooleanAnd($equal, $smaller);

		$coverageFactory = $this->mockCoverageFactory();
		$coverageFactory->expects($this->at(0))->method('createCoverageForNode')->with($this->equalTo($equal))
			->will($this->returnValue($this->mockCoverage()));
		$coverageFactory->expects($this->at(1))->method('createCoverageForNode')->with($this->equalTo($smaller))
			->will($this->returnValue($this->mockCoverage()));
		$coverageFactory->expects($this->once())->method('createCoverageForDecision')->with($this->equalTo($expression))
			->will($this->returnValue($this->mockDecisionCoverage()));

		$subject = new CoverageBuilderFactory($this->mockEventDispatcher(), $coverageFactory);
		$subject->createBuilderForDecision($expression);
	}

	/**
	 * @test
	 */
	public function createdDecisionCoverageBuilderIsAddedAsEventSubscriber() {
		$equal = $this->mockExpressionNode('BinaryOp\\Equal');
		$smaller = $this->mockExpressionNode('BinaryOp\\Smaller');
		$expression = new BooleanAnd($equal, $smaller);

		$mockedEventDispatcher = $this->mockEventDispatcher();
		$mockedEventDispatcher->expects($this->at(0))->method('addSubscriber')
			->with($this->isInstanceOf('AndreasWolf\DecisionCoverage\Coverage\Builder\SingleConditionCoverageBuilder'));
		$mockedEventDispatcher->expects($this->at(1))->method('addSubscriber')
			->with($this->isInstanceOf('AndreasWolf\DecisionCoverage\Coverage\Builder\SingleConditionCoverageBuilder'));
		$mockedEventDispatcher->expects($this->at(2))->method('addSubscriber')
			->with($this->isInstanceOf('AndreasWolf\DecisionCoverage\Coverage\Builder\DecisionCoverageBuilder'));

		$subject = new CoverageBuilderFactory($mockedEventDispatcher, $this->mockCoverageFactory());
		$subject->createBuilderForDecision($expression);
	}

	/**
	 *
	 * @param string $expressionType The type (class name) of the expression (= the part after "\PhpParser\Node\Expr\")
	 * @return Expr
	 */
	protected function mockExpressionNode($expressionType) {
		return $this->getMockBuilder('PhpParser\\Node\\Expr\\' . $expressionType)->disableOriginalConstructor()->getMock();
	}

}

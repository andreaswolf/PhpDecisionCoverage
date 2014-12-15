<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Coverage\Evaluation;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\DecisionEvaluationDirector;
use AndreasWolf\DecisionCoverage\Tests\Helpers\DataSampleBuilder;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;


class DecisionEvaluationDirectorTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function booleanAndIsCorrectlyEvaluated() {
		$builder = new DataSampleBuilder();
		$builder->addMockedExpression('A'); $builder->addMockedExpression('B');
		$builder->addSampleValue('A', TRUE); $builder->addSampleValue('B', FALSE);

		$expression = new BooleanAnd($builder->getExpressionMock('A'), $builder->getExpressionMock('B'));
		$sample = $builder->getSample();

		$subject = new DecisionEvaluationDirector($expression);
		$output = $subject->evaluate($sample);

		$this->assertTrue($output->getInputForExpression('A')->getValue());
		$this->assertFalse($output->getInputForExpression('B')->getValue());
		$this->assertFalse($output->getOutputValue());
	}

	/**
	 * @test
	 */
	public function shortCircuitOnSameLevelIsCorrectlyMarked() {
		$builder = new DataSampleBuilder();
		$builder->addMockedExpression('A'); $builder->addMockedExpression('B');
		$builder->addSampleValue('A', FALSE); $builder->addSampleValue('B', FALSE);

		$expression = new BooleanAnd($builder->getExpressionMock('A'), $builder->getExpressionMock('B'));
		$sample = $builder->getSample();

		$subject = new DecisionEvaluationDirector($expression);
		$output = $subject->evaluate($sample);

		$this->assertTrue($output->getInputForExpression('A')->isEvaluated());
		$this->assertFalse($output->getInputForExpression('B')->isEvaluated());
	}

	/**
	 * @test
	 */
	public function shortCircuitInLeftExpressionPartAlsoDisablesEvaluationForNestedLevelsInRightExpressionPart() {
		$builder = new DataSampleBuilder();
		$builder->addMockedExpression('A'); $builder->addMockedExpression('B'); $builder->addMockedExpression('C');
		$builder->addSampleValue('A', FALSE); $builder->addSampleValue('B', FALSE);
		$builder->addSampleValue('C', FALSE);

		$or = new BooleanOr($builder->getExpressionMock('B'), $builder->getExpressionMock('C'));
		$or->setAttribute('coverage__nodeId', 'D');
		// A && (B || C)
		$expression = new BooleanAnd($builder->getExpressionMock('A'), $or);
		$expression->setAttribute('coverage__nodeId', 'E');
		$sample = $builder->getSample();

		$subject = new DecisionEvaluationDirector($expression);
		$output = $subject->evaluate($sample);

		$this->assertFalse($output->getOutputValue());
		$this->assertFalse($output->getInputForExpression('B')->isEvaluated());
		$this->assertFalse($output->getInputForExpression('C')->isEvaluated());
	}

	/**
	 * @test
	 */
	public function shortCircuitBecauseOfDecisionInLeftPartBubblesUpToRightPartOfRootNode() {
		$builder = new DataSampleBuilder();
		$builder->addMockedExpression('A'); $builder->addMockedExpression('B'); $builder->addMockedExpression('C');
		$builder->addSampleValue('A', FALSE); $builder->addSampleValue('B', FALSE);
		$builder->addSampleValue('C', FALSE);

		$or = new BooleanOr($builder->getExpressionMock('A'), $builder->getExpressionMock('B'));
		$or->setAttribute('coverage__nodeId', 'D');
		$expression = new BooleanAnd($or, $builder->getExpressionMock('C'));
		$expression->setAttribute('coverage__nodeId', 'E');
		$sample = $builder->getSample();

		$subject = new DecisionEvaluationDirector($expression);
		$output = $subject->evaluate($sample);

		$this->assertFalse($output->getOutputValue());
		$this->assertFalse($output->getInputForExpression('C')->isEvaluated());
	}

	/**
	 * @test
	 */
	public function shortCircuitInThreeValuedAndCorrectlyGetsToAllRemainingParts() {
		$builder = new DataSampleBuilder();
		$builder->addMockedExpression('A'); $builder->addMockedExpression('B'); $builder->addMockedExpression('C');
		$builder->addSampleValue('A', FALSE); $builder->addSampleValue('B', FALSE);
		$builder->addSampleValue('C', FALSE);

		// A && B && C = (A && B) && C
		$leftAnd = new BooleanAnd($builder->getExpressionMock('A'), $builder->getExpressionMock('B'));
		$leftAnd->setAttribute('coverage__nodeId', 'D');
		$expression = new BooleanAnd($leftAnd, $builder->getExpressionMock('C'));
		$expression->setAttribute('coverage__nodeId', 'E');
		$sample = $builder->getSample();

		$subject = new DecisionEvaluationDirector($expression);
		$output = $subject->evaluate($sample);

		$this->assertFalse($output->getOutputValue());
		$this->assertFalse($output->getInputForExpression('B')->isEvaluated());
		$this->assertFalse($output->getInputForExpression('C')->isEvaluated());
	}

}

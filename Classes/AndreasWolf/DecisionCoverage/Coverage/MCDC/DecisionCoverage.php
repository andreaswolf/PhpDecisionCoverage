<?php
namespace AndreasWolf\DecisionCoverage\Coverage\MCDC;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\DecisionSample;
use AndreasWolf\DecisionCoverage\Coverage\ExpressionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use PhpParser\Node\Expr;


class DecisionCoverage extends ExpressionCoverage {

	/**
	 * @var Expr\BinaryOp
	 */
	protected $expression;

	/**
	 * @var DecisionInput[]
	 */
	protected $feasibleInputs;

	/**
	 * The IDs of all conditions inside the decision covered by this object.
	 *
	 * @var string[]
	 */
	protected $conditionIds;

	/**
	 * @var DecisionSample[]
	 */
	protected $samples;


	/**
	 * @param Expr $expression The covered expression
	 * @param string[] $conditionIds The condition ids this decision contains
	 * @param DecisionInput[] $feasibleInputs All inputs the decision covered by this object can possibly have.
	 */
	public function __construct(Expr $expression, $conditionIds, array $feasibleInputs) {
		parent::__construct($expression);

		$this->conditionIds = $conditionIds;
		$this->feasibleInputs = $feasibleInputs;
	}

	/**
	 * @param DecisionSample $sample
	 * @return void
	 */
	public function addSample(DecisionSample $sample) {
		$this->samples[] = $sample;
	}

	/**
	 * @return float The coverage as a value between 0 and 1.
	 */
	public function getCoverage() {
		// TODO: Implement getCoverage() method.
	}

}

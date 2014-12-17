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
	 * The list of feasible inputs for the decision.
	 *
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
	 * The samples that cover this decision.
	 *
	 * @var DecisionSample[]
	 */
	protected $samples;

	/**
	 * Mapping from the index of a sample to the index of the feasible input.
	 *
	 * This is used for determining the coverage.
	 *
	 * @var array
	 */
	protected $sampleToInputMap = array();


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
	 * @return Expr\BinaryOp
	 */
	public function getExpression() {
		return $this->expression;
	}

	/**
	 * @param DecisionSample $sample
	 * @return void
	 */
	public function addSample(DecisionSample $sample) {
		$this->samples[] = $sample;
		foreach ($this->feasibleInputs as $inputIndex => $input) {
			if ($sample->coversDecisionInput($input)) {
				$this->sampleToInputMap[] = $inputIndex;
			}
		}
	}

	/**
	 * @return float The coverage as a value between 0 and 1.
	 */
	public function getCoverage() {
		// all feasible inputs have to be covered
		return (float)count(array_unique(array_values($this->sampleToInputMap))) / count($this->feasibleInputs);
	}

}

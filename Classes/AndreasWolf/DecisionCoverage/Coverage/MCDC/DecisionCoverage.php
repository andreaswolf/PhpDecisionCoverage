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
	protected $samples = array();

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
	 * @param DecisionInput $input
	 * @return bool
	 */
	public function isCovered(DecisionInput $input) {
		foreach ($this->sampleToInputMap as $inputIndex) {
			$coveredInput = $this->feasibleInputs[$inputIndex];

			if ($coveredInput->equalTo($input)) {
				return TRUE;
			}
		}

		// no sample matched
		return FALSE;
	}

	/**
	 * @return float The coverage as a value between 0 and 1.
	 */
	public function getCoverage() {
		// all feasible inputs have to be covered at least once, but should only be counted once.
		return (float)($this->countUniqueCoveredInputs() / count($this->feasibleInputs));
	}

	/**
	 * Returns all feasible, unique inputs for the decision.
	 *
	 * These inputs already respect short-circuits and thus are the minimum full-coverage set for this decision.
	 *
	 * @return \AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput[]
	 */
	public function getFeasibleInputs() {
		return $this->feasibleInputs;
	}

	/**
	 * Returns the samples for this decision that were fetched from the program.
	 *
	 * @return DecisionSample[]
	 */
	public function getSamples() {
		return $this->samples;
	}

	/**
	 * Returns all samples that cover the given decision input.
	 *
	 * @param DecisionInput $input The input to cover. This must exactly match one of the decision’s feasible inputs,
	 *   otherwise the input will not be found.
	 * @return DecisionSample[]
	 */
	public function getSamplesForInput(DecisionInput $input) {
		$searchedInputIndex = array_search($input, $this->feasibleInputs);
		$samples = [];
		foreach ($this->sampleToInputMap as $sampleIndex => $inputIndex) {
			if ($inputIndex == $searchedInputIndex) {
				$samples[] = $this->samples[$sampleIndex];
			}
		}

		return $samples;
	}

	/**
	 * Returns the number of covered inputs, with each input only counted once.
	 *
	 * @return int
	 */
	public function countUniqueCoveredInputs() {
		return count(array_unique(array_values($this->sampleToInputMap)));
	}

	/**
	 * Returns the number of input combinations for full coverage of this decision.
	 *
	 * @return int
	 */
	public function countFeasibleInputs() {
		return count($this->feasibleInputs);
	}

}

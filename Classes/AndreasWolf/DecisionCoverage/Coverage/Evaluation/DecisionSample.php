<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\ConditionSample;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use PhpParser\Node\Expr;


/**
 * A combination of input values for a decision.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DecisionSample {

	/**
	 * @var DecisionInput
	 */
	protected $input;

	/**
	 * @var boolean
	 */
	protected $outputValue;

	/**
	 * @var DataSample
	 */
	protected $dataSample;

	/**
	 * @var string[]
	 */
	protected $skippedVariables;


	/**
	 * @param DecisionInput $input The input values, with the expression ID as key.
	 * @param string[] $shortedVariables
	 * @param boolean $outputValue
	 * @param DataSample $dataSample The data sample this sample belongs to. Necessary to create a relation to the test run.
	 */
	public function __construct($input, $shortedVariables, $outputValue, DataSample $dataSample) {
		$this->input = $input;
		$this->skippedVariables = $shortedVariables;
		$this->outputValue = $outputValue;
		$this->dataSample = $dataSample;
	}

	/**
	 * @param string $expressionOrNodeId The condition object or its node id.
	 * @return bool
	 */
	public function getInputForExpression($expressionOrNodeId) {
		// TODO convert expression to node id
		return $this->input->getValueForCondition($expressionOrNodeId);
	}

	/**
	 * Checks if the given variable was evaluated. If it was not, it will still have a value; that value was just
	 * not used for determining the decision outcome.
	 *
	 * @param string $condition The node ID of the condition.
	 * @return bool
	 */
	public function isEvaluated($condition) {
		return !$this->isSkipped($condition);
	}

	/**
	 * Checks if the variable was skipped, i.e. not evaluated.
	 *
	 * @param string $condition
	 * @return bool
	 */
	public function isSkipped($condition) {
		return in_array($condition, $this->skippedVariables);
	}

	/**
	 * @return boolean
	 */
	public function getOutputValue() {
		return $this->outputValue;
	}

	/**
	 * Returns the test this sample was fetched in.
	 *
	 * @return \AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\Test
	 */
	public function getTest() {
		return $this->dataSample->getTest();
	}

	/**
	 * Checks if the given input is covered by this sample.
	 *
	 * @param DecisionInput|array $input An input object or an array of condition ids and values.
	 * @return bool
	 */
	public function coversDecisionInput($input) {
		if ($input instanceof DecisionInput) {
			$values = $input->getInputs();
		} else {
			$values = $input;
		}

		foreach ($values as $expressionId => $value) {
			if ($this->input->getValueForCondition($expressionId) !== $value) {
				return FALSE;
			}
		}
		return TRUE;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\ConditionSample;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;


/**
 * A combination of input values for a decision.
 *
 * @author Andreas Wolf <aw@foundata.net>
 * TODO merge this class with DecisionInput
 */
class DecisionSample {

	/**
	 * @var boolean[]
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
	 * @param boolean[] $input The input values, with the expression ID as key.
	 * @param boolean $outputValue
	 * @param DataSample $dataSample The data sample this sample belongs to. Necessary to create a relation to the test run.
	 */
	public function __construct(array $input, $outputValue, DataSample $dataSample) {
		$this->input = $input;
		$this->outputValue = $outputValue;
		$this->dataSample = $dataSample;
	}

	/**
	 * @param string $expressionOrNodeId The condition object or its node id.
	 * @return bool
	 */
	public function getInputForExpression($expressionOrNodeId) {
		// TODO convert expression to node id
		if (!isset($this->input[$expressionOrNodeId])) {
			throw new \InvalidArgumentException('No input for ' . $expressionOrNodeId);
		}

		return $this->input[$expressionOrNodeId];
	}

	/**
	 * @return boolean
	 */
	public function getOutputValue() {
		return $this->outputValue;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\ConditionOutput;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;


/**
 * A combination of input values for a decision.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DecisionOutput {

	/**
	 * @var ConditionOutput
	 */
	protected $inputValues;

	/**
	 * @var boolean
	 */
	protected $outputValue;

	/**
	 * @var DataSample
	 */
	protected $dataSample;


	/**
	 * @param ConditionOutput[] $inputValues
	 * @param boolean $outputValue
	 * @param DataSample $dataSample
	 */
	public function __construct(array $inputValues, $outputValue, DataSample $dataSample) {
		$this->inputValues = $inputValues;
		$this->outputValue = $outputValue;
		$this->dataSample = $dataSample;
	}

	public function getInputForExpression($expressionOrNodeId) {
		// TODO convert expression to node id

		return $this->inputValues[$expressionOrNodeId];
	}

	/**
	 * @return boolean
	 */
	public function getOutputValue() {
		return $this->outputValue;
	}

}

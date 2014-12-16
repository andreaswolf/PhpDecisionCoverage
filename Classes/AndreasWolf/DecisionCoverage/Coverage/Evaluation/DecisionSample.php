<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\ConditionSample;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;


/**
 * A combination of input values for a decision.
 *
 * @author Andreas Wolf <aw@foundata.net>
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
	 * @param boolean[] $input
	 * @param boolean $outputValue
	 * @param DataSample $dataSample
	 */
	public function __construct(array $input, $outputValue, DataSample $dataSample) {
		$this->input = $input;
		$this->outputValue = $outputValue;
		$this->dataSample = $dataSample;
	}

	public function getInputForExpression($expressionOrNodeId) {
		// TODO convert expression to node id

		return $this->input[$expressionOrNodeId];
	}

	/**
	 * @return boolean
	 */
	public function getOutputValue() {
		return $this->outputValue;
	}

}

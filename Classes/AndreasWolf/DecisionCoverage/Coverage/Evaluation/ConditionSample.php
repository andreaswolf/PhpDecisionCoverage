<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use PhpParser\Node\Expr;


class ConditionSample {

	/**
	 * The expression ID of the condition
	 *
	 * @var string
	 */
	protected $conditionId;

	/**
	 * @var DataSample
	 */
	protected $dataSample;

	/**
	 * The output value of the condition
	 *
	 * @var boolean
	 */
	protected $value;

	/**
	 * If set, the condition has been evaluated and produced the output stored in $value;
	 * if not, the evaluation was skipped due to a short-circuit caused by another, earlier expression that already
	 * determined the decision output.
	 *
	 * @var bool
	 */
	protected $evaluated = TRUE;


	public function __construct(Expr $expression, DataSample $sample, ExpressionValue $value) {
		$this->conditionId = $expression->getAttribute('coverage__nodeId');
		$this->dataSample = $sample;
		$this->value = $value->getRawValue();
	}

	/**
	 * @return boolean
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return DataSample
	 */
	public function getDataSample() {
		return $this->dataSample;
	}

	/**
	 * @return void
	 */
	public function shortCircuit() {
		$this->evaluated = FALSE;
	}

	/**
	 * @return bool
	 */
	public function isEvaluated() {
		return $this->evaluated;
	}

}

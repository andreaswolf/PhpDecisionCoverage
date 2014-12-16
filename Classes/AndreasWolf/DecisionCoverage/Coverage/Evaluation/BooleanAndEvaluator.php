<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use PhpParser\Node\Expr\BinaryOp;


class BooleanAndEvaluator implements DecisionEvaluator {

	/**
	 * @var BinaryOp
	 */
	protected $expression;

	/**
	 * @var array
	 */
	protected $nodeIds;

	/**
	 * @var boolean[]
	 */
	protected $inputValues;

	/**
	 * @var boolean
	 */
	protected $output;

	/**
	 * @var boolean
	 */
	protected $shorted = FALSE;


	/**
	 * @param BinaryOp|string[] $expressionOrNodeIds The boolean AND expression or the node IDs of the single operands.
	 */
	public function __construct($expressionOrNodeIds) {
		if ($expressionOrNodeIds instanceof BinaryOp\BooleanAnd) {
			$this->nodeIds = [$expressionOrNodeIds->left, $expressionOrNodeIds->right];
		} elseif (is_array($expressionOrNodeIds)) {
			$this->nodeIds = $expressionOrNodeIds;
		}
	}

	public function evaluate(DecisionInput $input) {
		$inputValues = [];
		foreach ($this->nodeIds as $nodeId) {
			$inputValues[] = $input->getValueForCondition($nodeId);
		}
		if ($inputValues[0] === FALSE) {
			$shortCircuited = TRUE;
			$output = FALSE;
			$lastEvaluatedExpression = $this->nodeIds[0];
		} elseif ($inputValues[0] === TRUE) {
			if ($inputValues[1] === NULL) {
				// TODO set flag in result instead of throwing exception
				throw new \RuntimeException('Right part of boolean AND has not been evaluated.');
			}
			$shortCircuited = FALSE;
			$output = $inputValues[1] === TRUE;
			$lastEvaluatedExpression = $this->nodeIds[1];
		} else {
			// TODO set flag in result instead of throwing exception
			throw new \RuntimeException('Left part of boolean AND has not been evaluated.');
		}
		return new EvaluationResult($output, $shortCircuited, $lastEvaluatedExpression);
	}

	/**
	 * @deprecated
	 */
	public function recordInputValue(ExpressionValue $value) {
		$this->inputValues[] = $value->getRawValue();
		if ($value->getRawValue() === FALSE) {
			$this->output = FALSE;
			$this->shorted = TRUE;
		}
	}

	/**
	 * @deprecated
	 */
	public function isShorted() {
		return $this->shorted;
	}

	/**
	 * @deprecated
	 */
	public function finishEvaluation() {
		if ($this->output === NULL) {
			$this->output = TRUE;
		}
	}

	/**
	 * @return boolean
	 * @deprecated
	 */
	public function getOutput() {
		if ($this->output === NULL) {
			throw new \InvalidArgumentException('Evaluation has not been finished.', 1417909000);
		}
		return $this->output;
	}

}

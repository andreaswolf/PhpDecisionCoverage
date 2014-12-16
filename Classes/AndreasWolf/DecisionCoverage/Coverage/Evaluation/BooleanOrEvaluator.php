<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;



use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use PhpParser\Node\Expr\BinaryOp;


class BooleanOrEvaluator implements DecisionEvaluator {

	/**
	 * @var BinaryOp
	 */
	protected $expression;

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
		if ($expressionOrNodeIds instanceof BinaryOp\BooleanOr) {
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
		if ($inputValues[0] === TRUE) {
			$shortCircuited = TRUE;
			$output = TRUE;
			$lastEvaluatedExpression = $this->nodeIds[0];
		} elseif ($inputValues[0] === FALSE) {
			if ($inputValues[1] === NULL) {
				// TODO set flag in result instead of throwing exception
				throw new \RuntimeException('Right part of boolean OR has not been evaluated.');
			}
			$shortCircuited = FALSE;
			$output = $inputValues[1] === TRUE;
			$lastEvaluatedExpression = $this->nodeIds[1];
		} else {
			// TODO set flag in result instead of throwing exception
			throw new \RuntimeException('Left part of boolean OR has not been evaluated.');
		}
		return new EvaluationResult($output, $shortCircuited, $lastEvaluatedExpression);
	}

	/**
	 * @deprecated
	 */
	public function recordInputValue(ExpressionValue $value) {
		$this->inputValues[] = $value->getRawValue();
		if ($value->getRawValue() === TRUE) {
			$this->output = TRUE;
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
			$this->output = FALSE;
		}
	}

	/**
	 * @return boolean
	 * @deprecated
	 */
	public function getOutput() {
		return $this->output;
	}

}

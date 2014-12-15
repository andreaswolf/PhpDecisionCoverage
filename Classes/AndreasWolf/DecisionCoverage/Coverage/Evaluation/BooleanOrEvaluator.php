<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;



use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
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


	public function __construct(BinaryOp $expression) {
		$this->expression = $expression;
	}

	public function recordInputValue(ExpressionValue $value) {
		$this->inputValues[] = $value->getRawValue();
		if ($value->getRawValue() === TRUE) {
			$this->output = TRUE;
			$this->shorted = TRUE;
		}
	}

	public function isShorted() {
		return $this->shorted;
	}

	public function finishEvaluation() {
		if ($this->output === NULL) {
			$this->output = FALSE;
		}
	}

	/**
	 * @return boolean
	 */
	public function getOutput() {
		return $this->output;
	}

}

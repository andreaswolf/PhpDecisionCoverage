<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;


use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use PhpParser\Node\Expr\BinaryOp;


class BooleanAndEvaluator implements DecisionEvaluator {

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
		if ($value->getRawValue() === FALSE) {
			$this->output = FALSE;
			$this->shorted = TRUE;
		}
	}

	public function isShorted() {
		return $this->shorted;
	}

	public function finishEvaluation() {
		if ($this->output === NULL) {
			$this->output = TRUE;
		}
	}

	/**
	 * @return boolean
	 */
	public function getOutput() {
		if ($this->output === NULL) {
			throw new \InvalidArgumentException('Evaluation has not been finished.', 1417909000);
		}
		return $this->output;
	}

}

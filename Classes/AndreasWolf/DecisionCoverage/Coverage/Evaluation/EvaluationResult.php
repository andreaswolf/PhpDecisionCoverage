<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use PhpParser\Node\Expr;


/**
 * The result of the evaluation of a boolean expression.
 *
 * @author Andreas Wolf <aw@fondata.net>
 */
class EvaluationResult {

	/**
	 * @var boolean
	 */
	protected $value;

	/**
	 * If set, evaluation was interrupted prematurely because the result was already determined.
	 *
	 * @var boolean
	 */
	protected $shortCircuited;

	/**
	 * The ID of the last expression that was evaluated
	 *
	 * @var string
	 */
	protected $lastEvaluatedExpression;

	/**
	 * @param boolean $value
	 * @param boolean $shortCircuited
	 * @param Expr|string $lastEvaluatedExpression
	 */
	public function __construct($value, $shortCircuited, $lastEvaluatedExpression) {
		$this->value = $value;
		$this->shortCircuited = $shortCircuited;
		$this->lastEvaluatedExpression = $lastEvaluatedExpression;
	}

	/**
	 * @return string
	 */
	public function getLastEvaluatedExpression() {
		return $this->lastEvaluatedExpression;
	}

	/**
	 * @return boolean
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return boolean
	 */
	public function isShortCircuited() {
		return $this->shortCircuited;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Input;

use PhpParser\Node\Expr;


/**
 * A feasible combination of input values for a decision.
 *
 * TODO add the output value/a getter for the output value (= the top-level decision)
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DecisionInput {

	/**
	 * The input (= condition) values as captured from the program.
	 *
	 * @var boolean[]
	 */
	protected $inputs = array();

	/**
	 * Values of decisions within this decision. This is used to store e.g. the value of (A || B) in the expression
	 * (A || B) && C, so we have a basis for further evaluation of the decision.
	 *
	 * This can also be used for storing the final value of the decision, but this is currently not implemented (would
	 * belong to DecisionInputBuilder)
	 *
	 * @var boolean[]
	 */
	protected $decisionValues = array();

	/**
	 * @var int
	 */
	protected $shortCircuit;


	/**
	 * @param Expr|string $condition The condition (or its node id)
	 * @param boolean $input
	 * @return DecisionInput A new instance of this class, with the new value added
	 */
	public function addInputForCondition($condition, $input) {
		$newInput = new self();
		$newInput->inputs = array_merge($this->inputs, [$this->getNodeId($condition) => $input]);
		$newInput->shortCircuit = $this->shortCircuit;
		$newInput->decisionValues = $this->decisionValues;

		return $newInput;
	}

	/**
	 * Sets the value for the given decision, derived from the condition values. This value is derived, therefore we can
	 * set it directly in this object and don’t have to return a new instance.
	 *
	 * @param Expr|string $decision The decision (or its node id)
	 * @param boolean $value
	 * @return void
	 */
	public function setValueForDecision($decision, $value) {
		$this->decisionValues[$this->getNodeId($decision)] = $value;
	}

	/**
	 * @param Expr|string $condition
	 * @return bool
	 */
	public function getValueForCondition($condition) {
		$id = $this->getNodeId($condition);
		if (isset($this->inputs[$id])) {
			return $this->inputs[$id];
		} elseif (isset($this->decisionValues[$id])) {
			return $this->decisionValues[$id];
		}
	}

	/**
	 * @return boolean[]
	 */
	public function getInputs() {
		return $this->inputs;
	}

	/**
	 * @param int $shortCircuit
	 */
	public function setShortCircuit($shortCircuit) {
		$this->shortCircuit = $shortCircuit;
	}

	/**
	 * @return int
	 */
	public function getShortCircuit() {
		return $this->shortCircuit;
	}

	/**
	 * @param $condition
	 * @return string
	 */
	protected function getNodeId($condition) {
		if ($condition instanceof Expr) {
			return $condition->getAttribute('coverage__nodeId');
		}

		return $condition;
	}

}

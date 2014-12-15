<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Input;


use PhpParser\Node\Expr;


class DecisionInput {

	/**
	 * @var boolean[]
	 */
	protected $inputs = array();

	/**
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

<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use PhpParser\Node\Expr;


/**
 * Builder for decision inputs from a data sample.
 *
 * This class converts the data fetched from the debugger to a format usable for data analysis.
 *
 * @author Andreas Wolf <aw@foundata.net>
 * TODO find a better name for this class
 */
class DataSampleInputBuilder extends DecisionInputBuilder {

	/**
	 * @var DataSample
	 */
	protected $sample;

	/**
	 * @var array
	 */
	protected $shortedVariables = array();


	/**
	 * Builds the input object for the given data sample.
	 *
	 * The built input is the data sample without all shorted variables, i.e. only the variables that really influenced
	 * the decision output. This does however not mean that each of the values really influenced the decision output.
	 * (For example, for an AND, if both inputs were FALSE, only the first would have been evaluated, so the second
	 * is removed in this method. If the first however was TRUE, the decision output would still have been FALSE.)
	 *
	 * @param Expr\BinaryOp $coveredExpression The syntax tree of the decision to build the input for. All decision and condition nodes in this tree need to have an ID set in the attribute coverage__nodeId.
	 * @param DataSample $sample
	 * @return DecisionInput The decision input built for the data sample.
	 */
	public function buildInputForSample(Expr\BinaryOp $coveredExpression, DataSample $sample) {
		$this->sample = $sample;

		parent::buildInput($coveredExpression);

		return $this->builtInputs[0];
	}

	/**
	 * @return array
	 */
	public function getShortedVariables() {
		return $this->shortedVariables;
	}

	protected function buildInputForVariables($position, DecisionInput $inputs) {
		if ($position >= count($this->conditions)) {
			$this->builtInputs[] = $inputs;
			return;
		}

		$currentVariable = $this->conditions[$position];
		$variableNode = $this->getNodeFromMarkedTree($currentVariable);

		if ($this->isShorted($inputs, $currentVariable)) {
			$this->log->debug("Variable $currentVariable is shorted, continuing with next variable");
			$this->shortedVariables[] = $currentVariable;
			$this->buildInputForVariables($position + 1, $inputs);
		} else {
			$value = $this->sample->getValueFor($currentVariable)->getRawValue();
			$inputs = $inputs->addInputForCondition($currentVariable, $value);

			$this->evaluateDecision($inputs, $this->findParentDecision($variableNode));

			$this->buildInputForVariables($position + 1, $inputs);
		}
	}

}

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
	 * Builds the available inputs for the given decision. As this class respects short-circuit evaluation, this is
	 * not the outer product (dyadic product) of TRUE/FALSE values for each variable, but only contains variables that
	 * really influence the output.
	 *
	 * @param Expr\BinaryOp $coveredExpression The syntax tree of the decision to build the input for. All decision and condition nodes in this tree need to have an ID set in the attribute coverage__nodeId.
	 * @param DataSample $sample
	 * @return DecisionInput The decision input built for this
	 */
	public function buildInputForSample(Expr\BinaryOp $coveredExpression, DataSample $sample) {
		$this->sample = $sample;

		parent::buildInput($coveredExpression);

		return $this->builtInputs[0];
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
			$this->buildInputForVariables($position + 1, $inputs);
		} else {
			$value = $this->sample->getValueFor($currentVariable)->getRawValue();
			$inputs = $inputs->addInputForCondition($currentVariable, $value);

			$this->evaluateDecision($inputs, $this->findParentDecision($variableNode));

			$this->buildInputForVariables($position + 1, $inputs);
		}
	}

}

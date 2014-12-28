<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Evaluation\BooleanAndEvaluator;
use AndreasWolf\DecisionCoverage\Coverage\Evaluation\BooleanOrEvaluator;
use AndreasWolf\DecisionCoverage\Coverage\Event\DataSampleEvent;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;
use AndreasWolf\DecisionCoverage\Coverage\Input\SyntaxTreeMarker;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use PhpParser\Node\Expr;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * A builder for the feasible input combinations of a decision.
 *
 * Short-circuits are respected, i.e. all shortened parts are left out in the combinations.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DecisionInputBuilder {

	/**
	 * @var LoggerInterface
	 */
	protected $log;

	/**
	 * The node IDs of the conditions in the syntax tree.
	 *
	 * @var string[]
	 */
	protected $conditions;

	/**
	 * The syntax tree, flattened to a list with l/r number values.
	 *
	 * See SyntaxTreeMarker for more information on the way the tree is built.
	 *
	 * The nodes in this array have various properties:
	 *   - their array *index*, used for directly accessing them in the array
	 *   - their *node id* (from coverage__nodeId)
	 *   - their *left and/or right value*
	 *
	 * @var array
	 */
	protected $markedTree;

	/**
	 * @var array
	 */
	protected $builtInputs = array();


	public function __construct(LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}
		$this->log = $logger;
	}

	/**
	 * Returns the IDs of all condition nodes within the decision the inputs were built for last.
	 *
	 * @return \string[] A list of node ids as used in the coverage__nodeId attribute of an AST.
	 */
	public function getConditions() {
		return $this->conditions;
	}

	/**
	 * Builds the available inputs for the given decision. As this class respects short-circuit evaluation, this is
	 * not the outer product (dyadic product) of TRUE/FALSE values for each variable, but only contains variables that
	 * really influence the output.
	 *
	 * @param Expr\BinaryOp $coveredExpression The syntax tree of the decision to build the input for. All decision and condition nodes in this tree need to have an ID set in the attribute coverage__nodeId.
	 * @return DecisionInput[] A list of feasible input combinations for the given decision, with the variables in the order in which the decision is evaluated.
	 */
	public function buildInput(Expr\BinaryOp $coveredExpression) {
		$marker = new SyntaxTreeMarker();
		$this->markedTree = $marker->markSyntaxTree($coveredExpression);

		$this->conditions = [];
		$this->builtInputs = [];

		foreach ($this->markedTree as $treeNode) {
			if (!array_key_exists('l', $treeNode) || !array_key_exists('r', $treeNode)) {
				// this is a condition => add variables
				$this->conditions[] = $treeNode['id'];
			}
		}

		$this->buildInputForVariables(0, new DecisionInput());
		return $this->builtInputs;
	}

	/**
	 * Checks the variable at the given position for possible inputs and recursively continues building with these
	 * inputs. Additionally, the inputs are checked for causing short-circuits. Short-circuited variables in an input
	 * are not set at all.
	 *
	 * After this method has run, $this->builtInputs contains a list of all inputs.
	 *
	 * @param int $position
	 * @param DecisionInput $inputs
	 */
	protected function buildInputForVariables($position, DecisionInput $inputs) {
		$this->log->debug("inputs: " . json_encode($inputs->getInputs()));
		if ($position >= count($this->conditions)) {
			$this->builtInputs[] = $inputs;
			return;
		}
		$currentVariable = $this->conditions[$position];
		$variableNode = $this->getNodeFromMarkedTree($currentVariable);
		$this->log->debug("Building input for variable " . $currentVariable);

		if ($this->isShorted($inputs, $currentVariable)) {
			$this->log->debug("Variable $currentVariable is shorted, continuing with next variable");
			$this->buildInputForVariables($position + 1, $inputs);
		} else {
			// TODO limit this to feasible inputs
			$trueInput = $inputs->addInputForCondition($currentVariable, TRUE);
			$falseInput = $inputs->addInputForCondition($currentVariable, FALSE);

			$decision = $this->findParentDecision($variableNode);
			$this->evaluateDecision($trueInput, $decision);
			$this->evaluateDecision($falseInput, $decision);

			$this->buildInputForVariables($position + 1, $trueInput);

			$this->buildInputForVariables($position + 1, $falseInput);
		}
	}

	/**
	 * Finds the decision node directly above any given node.
	 *
	 * @param array $node
	 * @return array The decision node
	 */
	protected function findParentDecision($node) {
		$nodeId = isset($node['l']) ? $node['l'] : $node['r'];
		$nodeIndex = $this->getArrayIndexFromMarkedTree($node['id']);

		foreach ($this->markedTree as $treeNode) {
			if (!isset($treeNode['l']) || !isset($treeNode['r'])) {
				continue;
			}
			if (isset($treeNode['l']) && $treeNode['l'] + 1 == $nodeId) {
				return $treeNode;
			}
			if (isset($treeNode['r']) && $treeNode['r'] - 1 == $nodeId) {
				return $treeNode;
			}
		}
	}

	/**
	 * Checks if the given variable is shorted in the given input.
	 *
	 * @param DecisionInput $input
	 * @param string $variable The node ID of the variable to check.
	 * @return bool
	 */
	protected function isShorted(DecisionInput $input, $variable) {
		if ($input->getShortCircuit() == 0) {
			return FALSE;
		}

		$variableNode = $this->getNodeFromMarkedTree($variable);
		// condition nodes only have left or right id set, so we need to check both
		if (isset($variableNode['r']) && $input->getShortCircuit() > $variableNode['r']) {
			return TRUE;
		}
		if (isset($variableNode['l']) && $input->getShortCircuit() > $variableNode['l']) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param string $id
	 * @return array
	 */
	protected function getNodeFromMarkedTree($id) {
		foreach ($this->markedTree as $index => $treeItem) {
			if ($treeItem['id'] == $id) {
				return $treeItem;
			}
		}
		throw new \RuntimeException('Node ' . $id . ' not found.');
	}

	/**
	 * Returns the index of the tree node with the given id.
	 *
	 * @param string $id
	 * @return int
	 */
	protected function getArrayIndexFromMarkedTree($id) {
		foreach ($this->markedTree as $index => $treeItem) {
			if ($treeItem['id'] == $id) {
				return $index;
			}
		}
		throw new \RuntimeException('Node ' . $id . ' not found.');
	}

	/**
	 * @param int $value
	 * @return array
	 */
	protected function getNodeByLeftRightValue($value) {
		foreach ($this->markedTree as $index => $treeItem) {
			if ((isset($treeItem['l']) && $treeItem['l'] == $value)
				|| (isset($treeItem['r']) && $treeItem['r'] == $value)) {

				return $treeItem;
			}
		}
	}


	/**
	 * Traverse the tree backwards beginning at the node with the given node left/right value, returning the first
	 * decision that is found. Due to the ordering of nodes, this will always return the decision directly above the
	 * given node id. This will also work for decisions.
	 *
	 * @param $nodeNumber
	 * @return array
	 */
	protected function findDecisionBeforeNodeInMarkedTree($nodeNumber) {
		$i = $nodeNumber;
		while ($i > 0) {
			--$i;
			$node = $this->markedTree[$i];
			if (array_key_exists('l', $node) && array_key_exists('r', $node)) {
				return $node;
			}
		}
	}

	/**
	 * Traverse the tree forward beginning at the node with the given node left/right value, returning the first
	 * decision that is found. Due to the ordering of nodes, this will always return the decision directly above the
	 * given node id. This will also work for decisions.
	 *
	 * @param $nodeNumber
	 * @return array
	 */
	protected function findDecisionAfterNodeInMarkedTree($nodeNumber) {
		$i = $nodeNumber;
		$nodeCount = count($this->markedTree);
		while ($i < $nodeCount - 1) {
			++$i;
			$node = $this->markedTree[$i];
			if (array_key_exists('l', $node) && array_key_exists('r', $node)) {
				return $node;
			}
		}
	}

	protected function evaluateDecision(DecisionInput $input, $decisionLeftRightValue) {
		if (is_array($decisionLeftRightValue)) {
			$decisionNode = $decisionLeftRightValue;
		} else {
			$decisionNode = $this->getNodeByLeftRightValue($decisionLeftRightValue);
		}
		$this->log->debug("Evaluating decision " . $decisionNode['id']);

		$conditionNodes = [
			$this->getNodeByLeftRightValue($decisionNode['l'] + 1),
			$this->getNodeByLeftRightValue($decisionNode['r'] - 1),
		];
		$values = [];
		// TODO move this to its own class structure
		foreach ($conditionNodes as $conditionNode) {
			// TODO we need to check if the value has not been set at all, if yes, we can possibly not evaluate this
			// decision
			$values[] = $input->getValueForCondition($conditionNode['id']);
		}
		$this->log->debug("Decision input values: " . json_encode($values));
		try {
			if ($decisionNode['type'] == 'Expr_BinaryOp_BooleanAnd') {
				$evaluator = new BooleanAndEvaluator(array($conditionNodes[0]['id'], $conditionNodes[1]['id']));
			} elseif ($decisionNode['type'] == 'Expr_BinaryOp_BooleanOr') {
				$evaluator = new BooleanOrEvaluator(array($conditionNodes[0]['id'], $conditionNodes[1]['id']));
			} else {
				throw new \RuntimeException('Unsupported decision type "' . $decisionNode['type'] . '"');
			}
			$result = $evaluator->evaluate($input);
			$decisionValue = $result->getValue();
			if ($result->isShortCircuited()) {
				$this->log->debug('Shorted decision ' . $decisionNode['id']);
				$input->setShortCircuit($decisionNode['r']);
			}
		} catch (\RuntimeException $e) {
			$this->log->debug('Could not evaluate decision: ' . $e->getMessage());
			return;
		}
		if (isset($decisionValue)) {
			$this->log->debug('Determined decision to be ' . var_export($decisionValue, TRUE));
			$input->setValueForDecision($decisionNode['id'], $decisionValue);
			$nextDecision = $this->findParentDecision($decisionNode);
			if ($nextDecision) {
				$this->evaluateDecision($input, $nextDecision['l']);
			}
		}
	}

}

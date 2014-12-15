<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

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

			if (isset($variableNode['r'])) {
				// the current variable’s node is the right child of a decision, so we can determine the value of the
				// decision above
				$this->log->debug("Evaluating decision for node " . $variableNode['r']);
				$this->evaluateDecision($inputs, $variableNode['r'] + 1);
			}
		} else {
			// TODO limit this to feasible inputs
			$trueInput = $inputs->addInputForCondition($currentVariable, TRUE);
			$falseInput = $inputs->addInputForCondition($currentVariable, FALSE);

			if (isset($variableNode['r'])) {
				// the current variable’s node is the right child of a decision, so we can determine the value of the
				// decision above
				$this->log->debug("Evaluating decision for node " . $variableNode['r']);
				$this->evaluateDecision($trueInput, $variableNode['r'] + 1);
				$this->evaluateDecision($falseInput, $variableNode['r'] + 1);
			}

			$this->checkInputForShortCircuit($trueInput, $currentVariable, TRUE);
			$this->buildInputForVariables($position + 1, $trueInput);
			$this->checkInputForShortCircuit($falseInput, $currentVariable, FALSE);
			$this->buildInputForVariables($position + 1, $falseInput);
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
	protected function getIndexFromMarkedTree($id) {
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


	protected function checkInputForShortCircuit(DecisionInput $input, $variable, $value) {
		$variableNodeIndex = $this->getIndexFromMarkedTree($variable);

		$decision = $this->findDecisionBeforeNodeInMarkedTree($variableNodeIndex);
		$this->log->debug("Checking shorts for $variable, " . ($value == TRUE ? 'TRUE' : 'FALSE'));
		$this->log->debug("Found decision with node id " . $decision['id'] . " and type " . $decision['type']);
		if (($decision['type'] == 'Expr_BinaryOp_BooleanAnd' && $value == FALSE)
			|| ($decision['type'] == 'Expr_BinaryOp_BooleanOr' && $value == TRUE)) {

			// mark all nodes below the decision as short-circuited
			$input->setShortCircuit($decision['r']);
			$this->log->debug("Set short up to node " . $decision['r']);
		}
	}

	/**
	 * Traverse the tree backwards beginning at the node with the given node id, returning the first decision that is
	 * found. Due to the ordering of nodes, this will always return the decision directly above the given node id.
	 * This will also work for decisions.
	 *
	 * @param $nodeId
	 * @return array
	 */
	protected function findDecisionBeforeNodeInMarkedTree($nodeId) {
		$i = $nodeId;
		while ($i > 0) {
			--$i;
			$node = $this->markedTree[$i];
			if (array_key_exists('l', $node) && array_key_exists('r', $node)) {
				return $node;
			}
		}
	}

	/**
	 * Traverse the tree backwards beginning at the node with the given node id, returning the first decision that is
	 * found. Due to the ordering of nodes, this will always return the decision directly above the given node id.
	 * This will also work for decisions.
	 *
	 * @param $nodeId
	 * @return array
	 */
	protected function findDecisionAfterNodeInMarkedTree($nodeId) {
		throw new \RuntimeException('foo');
		$i = $nodeId;
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
		$decisionNode = $this->getNodeByLeftRightValue($decisionLeftRightValue);
		$this->log->debug("Evaluating decision " . $decisionNode['id']);

		$conditionNodes = [
			$this->getNodeByLeftRightValue($decisionNode['l'] + 1),
			$this->getNodeByLeftRightValue($decisionNode['r'] - 1),
		];
		$values = [];
		foreach ($conditionNodes as $conditionNode) {
			$values[] = $input->getValueForCondition($conditionNode['id']);
		}
		if ($decisionNode['type'] == 'Expr_BinaryOp_BooleanAnd') {
			$decisionValue = !in_array(FALSE, $values, TRUE);
		} elseif ($decisionNode['type'] == 'Expr_BinaryOp_BooleanOr') {
			$decisionValue = in_array(TRUE, $values, TRUE);
		}
		if (isset($decisionValue)) {
			$input->setValueForDecision($decisionNode['id'], $decisionValue);
			$this->checkInputForShortCircuit($input, $decisionNode['id'], $decisionValue);
		}
	}

}

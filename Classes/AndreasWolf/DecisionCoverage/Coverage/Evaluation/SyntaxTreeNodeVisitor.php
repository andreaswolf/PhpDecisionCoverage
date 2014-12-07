<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\StackingIterator;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\Event\IteratorEvent;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Visitor for determining the value of a decision.
 *
 * This node is attached to a stacking iterator. It records all encountered condition values and determines the
 * values of all decisions (the root of the AST and all intermediate branch nodes) consecutively. It also keeps
 * track of short circuits within a subtree and marks all subsequent conditions and subordinate decisions accordingly.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SyntaxTreeNodeVisitor implements EventSubscriberInterface {

	/**
	 * The condition values used for determining the decision value
	 *
	 * @var ConditionOutput[]
	 */
	protected $inputValues = array();

	/**
	 * The iterator used for traversing the node tree.
	 *
	 * @var StackingIterator
	 */
	protected $iterator;

	/**
	 * Temporary stack used while traversing the node tree.
	 *
	 * @var DecisionEvaluator[]
	 */
	protected $evaluatorStack = array();

	/**
	 * The evaluator used for determining the final expression result.
	 *
	 * @var DecisionEvaluator
	 */
	protected $rootEvaluator;

	/**
	 * The data sample that is evaluated.
	 *
	 * @var DataSample
	 */
	protected $sample;

	/**
	 * @var int
	 */
	protected $shortedLevel = -1;

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;


	public function __construct(StackingIterator $iterator, DataSample $sample, ExpressionService $expressionService = NULL) {
		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}

		$this->expressionService = $expressionService;
		$this->iterator = $iterator;
		$this->sample = $sample;
	}

	public function levelDownHandler(IteratorEvent $event) {
		// no need to check if $stackItem is a decision, as elements with children are always decisions
		$stackItem = $this->iterator->getLastStackElement();
		switch ($stackItem->getType()) {
			case 'Expr_BinaryOp_BooleanAnd':
				$evaluator = new BooleanAndEvaluator($stackItem);
				break;
			case 'Expr_BinaryOp_BooleanOr':
				$evaluator = new BooleanOrEvaluator($stackItem);
				break;
		}

		if (isset($evaluator)) {
			if (!$this->rootEvaluator) {
				$this->rootEvaluator = $evaluator;
			}
			array_unshift($this->evaluatorStack, $evaluator);
		}
	}

	public function levelUpHandler(IteratorEvent $event) {
		/** @var DecisionEvaluator $item */
		$item = array_shift($this->evaluatorStack);
		$item->finishEvaluation();
		if (count($this->evaluatorStack) > 0) {
			$this->evaluatorStack[0]->recordInputValue(new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, $item->getOutput()));
			$this->checkAndSetShortCircuit($event);
		}
	}

	public function nodeHandler(IteratorEvent $event) {
		$currentItem = $event->getIterator()->current();
		$this->checkAndResetShortCircuit($event);
		$this->checkAndSetShortCircuit($event);
		if (!$this->expressionService->isDecisionExpression($currentItem)) {
			$value = $this->sample->getValueFor($currentItem);
			$conditionOutput = new ConditionOutput($currentItem, $this->sample, $value);
			$this->inputValues[$currentItem->getAttribute('coverage__nodeId')] = $conditionOutput;
			if ($this->shortedLevel > 0) {
				$conditionOutput->shortCircuit();
			}

			$this->evaluatorStack[0]->recordInputValue($value);
			$this->checkAndSetShortCircuit($event);
		}
	}

	/**
	 * @return DecisionEvaluator
	 */
	public function getRootEvaluator() {
		return $this->rootEvaluator;
	}

	/**
	 * @return ConditionOutput[]
	 */
	public function getInputValues() {
		return $this->inputValues;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents() {
		return array(
			'children.begin' => 'levelDownHandler',
			'children.end' => 'levelUpHandler',
			'iteration.next' => 'nodeHandler',
		);
	}

	/**
	 * @param IteratorEvent $event
	 */
	protected function checkAndResetShortCircuit(IteratorEvent $event) {
		if ($event->getIterator()->getDepth() < $this->shortedLevel) {
			$this->shortedLevel = -1;
		}
	}

	/**
	 * @param IteratorEvent $event
	 */
	protected function checkAndSetShortCircuit(IteratorEvent $event) {
		if (count($this->evaluatorStack) == 0) {
			return;
		}

		// if we already have an active short circuit, we must not set it again to a (possibly deeper) level; if the
		// shorted level is deeper than where we currently are, the short circuit should already have been removed
		if ($this->evaluatorStack[0]->isShorted() && $this->shortedLevel == -1) {
			$this->shortedLevel = $event->getIterator()->getDepth();
		}
	}

}

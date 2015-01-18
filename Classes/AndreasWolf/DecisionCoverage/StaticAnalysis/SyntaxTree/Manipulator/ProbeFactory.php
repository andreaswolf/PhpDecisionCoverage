<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\NodeVisitor;
use PhpParser\Node;


/**
 * Node visitor that creates a breakpoint and value probe for decision statements.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ProbeFactory extends AbstractProbeFactory {

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;


	public function __construct(FileResult $analysis, ExpressionService $expressionService = NULL) {
		parent::__construct($analysis);

		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}

		$this->expressionService = $expressionService;
	}

	/**
	 * @param Node $node
	 * @return Node
	 */
	public function handleNode(Node $node) {
		if (!in_array($node->getType(), array('Stmt_If', 'Stmt_ElseIf'))) {
			return;
		}

		$conditionNode = $node->cond;
		$probe = $this->createBreakpoint($node);
		$this->addWatchExpressionsToBreakpoint($probe, $conditionNode);
		if (!$conditionNode->hasAttribute('coverage__cover')) {
			$conditionNode->setAttribute('coverage__cover', TRUE);
			$probe->addWatchedExpression($conditionNode);
		}

		$this->analysis->addBreakpoint($probe);
	}

	/**
	 * @param Probe $probe
	 * @param Node $rootNode
	 */
	protected function addWatchExpressionsToBreakpoint(Probe $probe, Node $rootNode) {
		$nodeIterator = new \RecursiveIteratorIterator(
			new SyntaxTreeIterator(array($rootNode), TRUE), \RecursiveIteratorIterator::SELF_FIRST
		);

		/** @var Node $node */
		foreach ($nodeIterator as $node) {
			if (!$node instanceof Node\Expr) {
				continue;
			}

			if ($this->expressionService->isDecisionExpression($node) || $this->expressionService->isRelationalExpression($node)) {
				$node->setAttribute('coverage__cover', TRUE);
				$probe->addWatchedExpression($node);
			}
			// TODO check if we want to cover single variables?
		}
	}

	/**
	 * @param Node $node
	 * @return Probe
	 */
	protected function createBreakpoint(Node $node) {
		$probe = new Probe($node->getLine());
		$node->setAttribute('coverage__probe', $probe);

		return $probe;
	}

}

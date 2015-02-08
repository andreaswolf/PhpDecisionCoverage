<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\DataCollectionProbe;
use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use PhpParser\Node;
use Psr\Log\LoggerInterface;


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


	public function __construct(FileResult $analysis, ExpressionService $expressionService = NULL,
	                            LoggerInterface $logger = NULL) {
		parent::__construct($analysis, $logger);

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

		/** @var Node\Expr $conditionNode */
		$conditionNode = $node->cond;
		$probe = $this->createDataCollectionProbe($node);
		$this->addWatchExpressionsToBreakpoint($probe, $conditionNode);
		if (!$conditionNode->hasAttribute('coverage__cover')) {
			$conditionNode->setAttribute('coverage__cover', TRUE);
			$probe->addWatchedExpression($conditionNode);
		}
	}

	/**
	 * @param DataCollectionProbe $probe
	 * @param Node $rootNode
	 */
	protected function addWatchExpressionsToBreakpoint(DataCollectionProbe $probe, Node $rootNode) {
		$watchStack = [$rootNode];

		$rootNode->setAttribute('coverage__cover', TRUE);
		$probe->addWatchedExpression($rootNode);

		while (count($watchStack) > 0) {
			$currentNode = array_shift($watchStack);

			if ($this->expressionService->isDecisionExpression($currentNode)) {
				$watchStack[] = $currentNode->left;
				$watchStack[] = $currentNode->right;
			} else {
				// the node is a condition
				$currentNode->setAttribute('coverage__cover', TRUE);
				$probe->addWatchedExpression($currentNode);
			}
		}
	}

	/**
	 * @param Node $node
	 * @return DataCollectionProbe
	 */
	protected function createDataCollectionProbe(Node $node) {
		$probe = new DataCollectionProbe($node->getLine());
		$this->attachProbeToNodeAndAnalysis($node, $probe);

		return $probe;
	}

}

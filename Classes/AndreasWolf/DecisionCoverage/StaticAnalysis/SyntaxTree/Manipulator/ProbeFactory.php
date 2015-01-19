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

		$this->analysis->addProbe($probe);
	}

	/**
	 * @param DataCollectionProbe $probe
	 * @param Node $rootNode
	 */
	protected function addWatchExpressionsToBreakpoint(DataCollectionProbe $probe, Node $rootNode) {
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
	 * @return DataCollectionProbe
	 */
	protected function createDataCollectionProbe(Node $node) {
		$probe = new DataCollectionProbe($node->getLine());
		$node->setAttribute('coverage__probe', $probe);

		return $probe;
	}

}

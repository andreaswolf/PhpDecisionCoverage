<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator;
use AndreasWolf\DecisionCoverage\Service\UuidService;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\NodeVisitor;
use PhpParser\Node;


/**
 * Adds a tree-unique node id to each traversed node of a syntax tree.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class NodeIdGenerator implements NodeVisitor {

	/**
	 * @var UuidService
	 */
	protected $uuidService;


	public function __construct(UuidService $uuidService = NULL) {
		if (!$uuidService) {
			$uuidService = new UuidService();
		}

		$this->uuidService = $uuidService;
	}

	/**
	 * Signal for the start of an instrumentation run.
	 *
	 * @param Node[] $rootNodes
	 * @return void
	 */
	public function startInstrumentation($rootNodes) {
	}

	/**
	 * Signal for the end of an instrumentation run.
	 *
	 * @param Node[] $rootNodes
	 * @return void
	 */
	public function endInstrumentation($rootNodes) {
	}

	/**
	 * @param Node $node
	 * @return Node
	 */
	public function handleNode(Node $node) {
		// use coverage__ as a kind of "pseudo-namespace" to not interfere with other attributes that might already
		// be set
		$node->setAttribute('coverage__nodeId', $this->uuidService->uuid4());
	}

}

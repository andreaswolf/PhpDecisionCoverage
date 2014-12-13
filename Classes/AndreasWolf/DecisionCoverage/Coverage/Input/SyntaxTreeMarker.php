<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Input;

use AndreasWolf\DecisionCoverage\Coverage\StackingIterator;
use AndreasWolf\DecisionCoverage\Event\IteratorEvent;
use AndreasWolf\DecisionCoverage\Source\DecisionIterator;
use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use PhpParser\Node\Expr;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * Marks the nodes of a syntax tree with numbers.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SyntaxTreeMarker {

	/**
	 * Creates a marked tree with left and right node ids for decisions and a left or right id for conditions.
	 *
	 * @param $rootNode
	 * @return array The list of marked nodes, with relations between them
	 */
	public function markSyntaxTree(Expr\BinaryOp $rootNode) {
		$markedTree = [];
		$nodeCounter = 0;
		$lastLevel = -1;
		$stackedNodeIndices = [];

		$dispatcher = new EventDispatcher();
		$dispatcher->addListener('children.begin', function(IteratorEvent $event) use (&$stackedNodeIndices, &$markedTree) {
			// remember the current node index (of the decision) in our tree to add the right ID to it later on
			$stackedNodeIndices[] = count($markedTree) - 1;
		});
		$dispatcher->addListener('children.end', function(IteratorEvent $event) use (&$markedTree, &$nodeCounter, &$stackedNodeIndices) {
			// add the right node ID to the decision after we have iterated over all its children
			$nodeId = array_pop($stackedNodeIndices);
			$markedTree[$nodeId]['r'] = ++$nodeCounter;
		});

		$iterator = new StackingIterator(new DecisionIterator($rootNode, TRUE),
			\RecursiveIteratorIterator::SELF_FIRST, 0, $dispatcher);
		foreach ($iterator as $node) {
			$nodeId = $node->getAttribute('coverage__nodeId');
			if ($lastLevel == $iterator->getDepth()) {
				// if the level was the same before, we must be on the right node now, as we only deal with binary trees
				$markedTree[] = ['r' => ++$nodeCounter, 'id' => $nodeId];
			} else {
				$markedTree[] = ['l' => ++$nodeCounter, 'id' => $nodeId];
			}
			$lastLevel = $iterator->getDepth();
		}

		return $markedTree;
	}

}

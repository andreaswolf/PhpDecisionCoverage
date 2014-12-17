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
 * The numbers do not correspond to array indices, but form an own hierarchy. The numbers are assigned on the left for
 * all left-hand side (LHS) children, on the right for all RHS children. This is used to detect if a value finally
 * determines the value of a decision (if it is the RHS value) or if it possibly short-circuits a decision (if it is
 * the LHS value). Decisions have both a left and right value, to make it easier to determine their children and find
 * them from a child (by going one number down/up).
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
			// we should not assign a right number if there are no subnodes
			if ($markedTree[$nodeId]['l'] < $nodeCounter) {
				$markedTree[$nodeId]['r'] = ++$nodeCounter;
			}
		});

		$iterator = new StackingIterator(new DecisionIterator($rootNode, TRUE),
			\RecursiveIteratorIterator::SELF_FIRST, 0, $dispatcher);
		foreach ($iterator as $node) {
			$nodeId = $node->getAttribute('coverage__nodeId');
			$newNode = ['id' => $nodeId, 'type' => $node->getType()];
			// only assign a right id now if we are at a leaf
			if ($lastLevel == $iterator->getDepth() && !$iterator->callHasChildren()) {
				// if the level was the same before, we must be at the right node now, as we only deal with binary trees
				$newNode['r'] = ++$nodeCounter;
			} else {
				$newNode['l'] = ++$nodeCounter;
			}
			$markedTree[] = $newNode;
			$lastLevel = $iterator->getDepth();
		}

		return $markedTree;
	}

}

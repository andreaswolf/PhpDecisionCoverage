<?php
namespace AndreasWolf\DecisionCoverage\Source;

use AndreasWolf\DecisionCoverage\Event\SyntaxTreeEvent;
use AndreasWolf\DecisionCoverage\Event\SyntaxTreeIteratorEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class RecursiveSyntaxTreeIterator extends \RecursiveIteratorIterator {

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;


	public function __construct(\Traversable $iterator, EventDispatcherInterface $eventDispatcher,
	                     $mode = \RecursiveIteratorIterator::LEAVES_ONLY, $flags = 0) {
		parent::__construct($iterator, $mode, $flags);

		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * This method is invoked when the traversal of an item’s children starts (i.e. before the first child is traversed).
	 *
	 * There are two events raised by this method:
	 *   - syntaxtree.level.enter (with the parent iterator)
	 *   - syntaxtree.level.entered (with the children iterator, placed at the first child)
	 */
	public function beginChildren() {
		$this->eventDispatcher->dispatch('syntaxtree.level.enter',
			new SyntaxTreeIteratorEvent($this->getSubIterator($this->getDepth() - 1))
		);
		parent::beginChildren();
		$this->eventDispatcher->dispatch('syntaxtree.level.entered',
			new SyntaxTreeIteratorEvent($this->getSubIterator())
		);
	}

	/**
	 * This method is invoked when the traversal of an item’s children end (i.e. after the last child has been traversed).
	 *
	 * There are two events raised by this method:
	 *   - syntaxtree.level.leave (with the children iterator, placed at the last child)
	 *   - syntaxtree.level.left (with the parent iterator)
	 */
	public function endChildren() {
		$this->eventDispatcher->dispatch('syntaxtree.level.leave',
			new SyntaxTreeIteratorEvent($this->getSubIterator())
		);
		parent::endChildren();
		// Note that the iterator is not valid within this function; we have reached the end of the list of children
		// and will continue with the next item on the level above after this method
		$this->eventDispatcher->dispatch('syntaxtree.level.left',
			new SyntaxTreeIteratorEvent($this->getSubIterator($this->getDepth() - 1))
		);
	}

}
 
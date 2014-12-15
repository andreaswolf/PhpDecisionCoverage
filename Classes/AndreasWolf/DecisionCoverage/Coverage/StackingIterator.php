<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DecisionCoverage\Event\IteratorEvent;
use PhpParser\Node\Expr;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Traversable;


/**
 * An iterator for binary trees using a depth-first approach.
 *
 * Depending on the $mode passed to the constructor, it can be used to do a pre- or postorder traversal (the node
 * itself before or after the children)
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class StackingIterator extends \RecursiveIteratorIterator {

	/**
	 * The last item encountered by the iterator.
	 *
	 * We need to store it because the iterator does not return a current item when the current children have finished.
	 *
	 * @var Expr
	 */
	protected $currentItem;

	/**
	 * The list of items in the hierarchy that lead to the current level.
	 *
	 * @var Expr[]
	 */
	protected $stack = array();

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;


	public function __construct(Traversable $iterator, $mode = \RecursiveIteratorIterator::LEAVES_ONLY, $flags = 0,
	                            EventDispatcherInterface $eventDispatcher = NULL) {
		if (!$eventDispatcher) {
			$eventDispatcher = new EventDispatcher();
		}
		$this->eventDispatcher = $eventDispatcher;

		parent::__construct($iterator, $mode, $flags);
	}

	public function beginIteration() {
		$this->eventDispatcher->dispatch('iteration.begin', new IteratorEvent($this));

		parent::beginIteration();
	}

	public function endIteration() {
		$this->eventDispatcher->dispatch('iteration.end', new IteratorEvent($this));

		parent::endIteration();
	}

	public function nextElement() {
		$this->eventDispatcher->dispatch('iteration.next', new IteratorEvent($this));

		parent::nextElement();
		$this->currentItem = parent::current();
	}

	public function beginChildren() {
		// current() would already return the first child here, so we need to stack the item we stored earlier
		$this->stack[] = $this->currentItem;

		$this->eventDispatcher->dispatch('children.begin', new IteratorEvent($this));

		parent::beginChildren();
	}

	/**
	 * Ends iteration for the list of items on the current level.
	 *
	 * The current item’s parent is still on the stack when the event is raised, so it can be fetched with
	 * getLastStackElement().
	 */
	public function endChildren() {
		$this->eventDispatcher->dispatch('children.end', new IteratorEvent($this));

		array_pop($this->stack);
		parent::endChildren();
	}

	/**
	 * Returns the stack of elements from the root to the current level.
	 *
	 * @return Expr[]
	 */
	public function getStack() {
		return $this->stack;
	}

	/**
	 * Returns the last stacked element, i.e. the current level’s parent item
	 *
	 * @return Expr
	 */
	public function getLastStackElement() {
		return $this->stack[count($this->stack) - 1];
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage;
use AndreasWolf\DecisionCoverage\Event\IteratorEvent;
use PhpParser\Node\Expr;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Traversable;


/**
 * An iterator for binary trees using the depth
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class StackingIterator extends \RecursiveIteratorIterator {

	protected $currentDepth = 0;

	protected $currentItem;

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

	public function endChildren() {
		$this->eventDispatcher->dispatch('children.end', new IteratorEvent($this));

		array_pop($this->stack);
		parent::beginChildren();
	}

	public function getStack() {
		return $this->stack;
	}

	/**
	 * @return Expr
	 */
	public function getLastStackElement() {
		return $this->stack[count($this->stack) - 1];
	}

}

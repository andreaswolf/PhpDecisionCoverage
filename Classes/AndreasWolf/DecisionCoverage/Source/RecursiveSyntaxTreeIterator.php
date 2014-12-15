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

	public function next() {
		parent::next();
	}

	public function beginChildren() {
		parent::beginChildren();
		$this->eventDispatcher->dispatch('syntaxtree.level.entered', new SyntaxTreeIteratorEvent($this->getSubIterator()));
	}

	public function endChildren() {
		parent::endChildren();
		// Note that the iterator is not valid within this function; we have reached the end of the list of children
		// and will continue with the next item on the level above after this method
		$this->eventDispatcher->dispatch('syntaxtree.level.left', new SyntaxTreeIteratorEvent($this->getSubIterator()));
	}

}
 
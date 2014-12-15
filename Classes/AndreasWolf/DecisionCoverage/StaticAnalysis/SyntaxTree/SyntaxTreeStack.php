<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree;

use AndreasWolf\DecisionCoverage\Event\SyntaxTreeIteratorEvent;
use PhpParser\Node;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class SyntaxTreeStack implements EventSubscriberInterface {

	/**
	 * @var Node[]
	 */
	protected $stack = array();

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;


	public function __construct(EventDispatcherInterface $eventDispatcher) {
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @param SyntaxTreeIteratorEvent $event
	 */
	public function levelEnteredHandler(SyntaxTreeIteratorEvent $event) {
		$newElement = $event->getIterator()->current();

		$this->stack[] = $newElement;

		$this->triggerEventForStackElement('entered', $event, $newElement);
	}

	public function levelLeftHandler(SyntaxTreeIteratorEvent $event) {
		$removedElement = array_pop($this->stack);

		$this->triggerEventForStackElement('left', $event, $removedElement);
	}

	/**
	 * @param SyntaxTreeIteratorEvent $event
	 * @param Node $newItem
	 */
	protected function triggerEventForStackElement($eventType, SyntaxTreeIteratorEvent $event, $newItem) {
		switch ($newItem->getType()) {
			case 'Stmt_Class':
				$this->eventDispatcher->dispatch('syntaxtree.class.' . $eventType, $event);
				break;

			case 'Stmt_ClassMethod':
				$this->eventDispatcher->dispatch('syntaxtree.classmethod.' . $eventType, $event);
				break;

			case 'Stmt_Function':
				$this->eventDispatcher->dispatch('syntaxtree.function.' . $eventType, $event);
				break;
		}
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'syntaxtree.level.entered' => 'levelEnteredHandler',
			'syntaxtree.level.left' => 'levelLeftHandler'
		);
	}

}
 
<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree;

use AndreasWolf\DecisionCoverage\Event\SyntaxTreeIteratorEvent;
use PhpParser\Node;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Keeps a stack of visited syntax tree nodes, dispatching events when certain nodes are entered/left.
 *
 * This can e.g. be used in conjunction with a RecursiveSyntaxTreeIterator instance, which dispatches the required
 * events on level entry/exit.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
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
		// do not register this object with the passed event dispatcher; this way, the passed dispatcher
		// can completely isolate this stack from the outer world
	}

	/**
	 * @param SyntaxTreeIteratorEvent $event
	 */
	public function levelEnterHandler(SyntaxTreeIteratorEvent $event) {
		$newElement = $event->getIterator()->current();

		$this->stack[] = $newElement;

		$this->triggerEventForStackElement('entered', $event, $newElement);
	}

	/**
	 * @param SyntaxTreeIteratorEvent $event
	 */
	public function levelLeftHandler(SyntaxTreeIteratorEvent $event) {
		$removedElement = array_pop($this->stack);

		$this->triggerEventForStackElement('left', $event, $removedElement);
	}

	/**
	 * @param string $eventType
	 * @param SyntaxTreeIteratorEvent $event
	 * @param Node $affectedNode
	 */
	protected function triggerEventForStackElement($eventType, SyntaxTreeIteratorEvent $event, $affectedNode) {
		switch ($affectedNode->getType()) {
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
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'syntaxtree.level.enter' => 'levelEnterHandler',
			'syntaxtree.level.left' => 'levelLeftHandler'
		);
	}

}

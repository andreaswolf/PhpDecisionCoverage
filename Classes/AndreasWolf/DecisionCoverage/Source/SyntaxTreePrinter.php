<?php
namespace AndreasWolf\DecisionCoverage\Source;

use AndreasWolf\DecisionCoverage\Coverage\StackingIterator;
use AndreasWolf\DecisionCoverage\Event\IteratorEvent;
use PhpParser\Node;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class SyntaxTreePrinter implements EventSubscriberInterface {

	protected $printProbes;

	public function printTree($tree, $printProbes = FALSE) {
		$eventDispatcher = new EventDispatcher();
		$eventDispatcher->addSubscriber($this);

		$this->printProbes = $printProbes;

		$iterator = new StackingIterator(new SyntaxTreeIterator($tree, TRUE), \RecursiveIteratorIterator::SELF_FIRST, 0,
			$eventDispatcher);

		foreach ($iterator as $node) {
			// handling is done via events
		}
	}

	public function treeNodeHandler(IteratorEvent $event) {
		$iterator = $event->getIterator();
		/** @var Node $currentItem */
		$currentItem = $iterator->current();
		$outputLine = function($text) use ($iterator) {
			echo str_repeat(' ', $iterator->getDepth() * 2), $text, "\n";
		};
		$line = $currentItem->getType();

		if (in_array('name', $currentItem->getSubnodeNames())) {
			$line .= " [" . $this->printName($currentItem) . "]";
		}
		if ($currentItem->hasAttribute('coverage__nodeId')) {
			$line .= " – node ID: " . $currentItem->getAttribute('coverage__nodeId');
		}
		$outputLine($line);

		if ($this->printProbes === TRUE && $currentItem->hasAttribute('coverage__probe')) {
			$probes = $currentItem->getAttribute('coverage__probe');
			$outputLine("    Probes: " . count($probes));

			foreach ($probes as $probe) {
				$outputLine("    - Probe: " . get_class($probe));
			}
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
			'iteration.next' => 'treeNodeHandler'
		);
	}

	/**
	 * @param $currentItem
	 * @return mixed
	 */
	protected function printName($currentItem) {
		if ($currentItem->name instanceof Node\Expr\Variable) {
			return $this->printName($currentItem->name);
		}
		return $currentItem->name;
	}


}

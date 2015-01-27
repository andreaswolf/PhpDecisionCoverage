<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\Event\SyntaxTreeIteratorEvent;
use AndreasWolf\DecisionCoverage\StaticAnalysis\CounterProbe;
use PhpParser\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Creates a probe for counting the number of method entries (i.e. invocations of a method).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class MethodEntryProbeFactory extends AbstractProbeFactory implements EventSubscriberInterface {

	protected $lastNodeWasMethodNode = FALSE;

	/**
	 * @param Node $node
	 * @return Node
	 */
	public function handleNode(Node $node) {
		// no-op, everything is handled on the classmethod entry event
	}

	/**
	 * Invoked when a syntax tree iterator reaches a method node.
	 *
	 * @param SyntaxTreeIteratorEvent $event
	 */
	public function methodEntryHandler(SyntaxTreeIteratorEvent $event) {
		/** @var Node\Stmt\ClassMethod|Node\Stmt\Function_ $methodNode */
		$methodNode = $event->getIterator()->current();

		$methodStatements = $methodNode->stmts;
		if (!is_array($methodStatements) || count($methodStatements) == 0) {
			$this->logger->debug('Could not add method entry point for ' . $methodNode->name . ': has no statements');
			return;
		}

		// make sure that coverage for this method will be calculated
		if (!$methodNode->hasAttribute('coverage__cover')) {
			$methodNode->setAttribute('coverage__cover', TRUE);
		}

		$firstStatement = $methodStatements[0];

		$this->addMethodEntryProbe($firstStatement);
	}

	/**
	 * Creates a method entry point probe for the given method
	 *
	 * @param Node\Stmt $statementInMethod
	 */
	protected function addMethodEntryProbe(Node\Stmt $statementInMethod) {
		$probe = new CounterProbe($statementInMethod->getLine());
		$this->attachProbeToNodeAndAnalysis($statementInMethod, $probe);

		$this->logger->debug('Added probe for method entry.');
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents() {
		return array(
			'syntaxtree.classmethod.entered' => 'methodEntryHandler',
		);
	}

}

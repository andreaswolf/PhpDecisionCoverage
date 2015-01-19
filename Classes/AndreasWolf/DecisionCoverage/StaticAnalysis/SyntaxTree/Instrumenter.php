<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree;

use AndreasWolf\DecisionCoverage\Source\RecursiveSyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * An instrumenter modifies a given Abstract Syntax Tree (AST) using the configured manipulators.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Instrumenter {

	/**
	 * @var NodeVisitor[]
	 */
	protected $visitors;

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;


	public function __construct(EventDispatcherInterface $eventDispatcher = NULL, LoggerInterface $logger = NULL) {
		if (!$eventDispatcher) {
			$eventDispatcher = new EventDispatcher();
		}
		if (!$logger) {
			$logger = new NullLogger();
		}

		$this->eventDispatcher = $eventDispatcher;
		$this->logger = $logger;
	}

	/**
	 * @param Node[] $nodes
	 */
	public function instrument(&$nodes) {
		$iterator = new RecursiveSyntaxTreeIterator(
			new SyntaxTreeIterator($nodes, TRUE), $this->eventDispatcher, \RecursiveIteratorIterator::SELF_FIRST
		);
		$syntaxTreeStack = new SyntaxTreeStack($this->eventDispatcher);

		foreach ($this->visitors as $manipulator) {
			$manipulator->startInstrumentation($nodes);
		}

		foreach ($iterator as $currentNode) {
			foreach ($this->visitors as $manipulator) {
				$manipulator->handleNode($currentNode);
			}
		}

		foreach ($this->visitors as $manipulator) {
			$manipulator->endInstrumentation($nodes);
		}
	}

	/**
	 * @param NodeVisitor $visitor
	 * @param int $precedence The precedence of this manipulator. The lower, the sooner this manipulator will be called.
	 */
	public function addVisitor(NodeVisitor $visitor, $precedence = 0) {
		$this->visitors[$precedence] = $visitor;

		if ($visitor instanceof EventSubscriberInterface) {
			$this->eventDispatcher->addSubscriber($visitor);
		}
	}

}

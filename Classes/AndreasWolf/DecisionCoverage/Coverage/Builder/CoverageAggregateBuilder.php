<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\ClassCoverage;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\Coverage\Event\FileCoverageEvent;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Event\SyntaxTreeIteratorEvent;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Keeps track of the node hierarchy during AST traversal and builds the nested (aggregating) coverage objects.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageAggregateBuilder implements EventSubscriberInterface {

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var CoverageBuilderFactory
	 */
	protected $builderFactory;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var CoverageSet
	 */
	protected $coverageSet;

	/**
	 * @var FileCoverage
	 */
	protected $currentFileCoverage;

	/**
	 * @var ClassCoverage
	 */
	protected $currentClassCoverage;

	/**
	 * @var MethodCoverage
	 */
	protected $currentMethodCoverage;


	public function __construct(EventDispatcherInterface $eventDispatcher, CoverageBuilderFactory $builderFactory,
	                            LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}

		$this->builderFactory = $builderFactory;
		$this->logger = $logger;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function fileEnteredHandler(FileCoverageEvent $event) {
		$this->currentFileCoverage = $event->getFileCoverage();
	}

	public function classEnteredHandler(SyntaxTreeIteratorEvent $event) {
		$classNode = $event->getIterator()->current();
		$this->logger->debug('Entered class ' . $classNode->name);
		$this->currentClassCoverage = new ClassCoverage($classNode->name);
		$this->currentFileCoverage->addCoverage($this->currentClassCoverage);
	}

	public function classLeftHandler(SyntaxTreeIteratorEvent $event) {
		$this->currentClassCoverage = NULL;
	}

	public function methodEnteredHandler(SyntaxTreeIteratorEvent $event) {
		/** @var Node\Stmt\ClassMethod $node */
		$node = $event->getIterator()->current();
		$this->logger->debug('Entered method ' . $node->name);
		$this->currentMethodCoverage = new MethodCoverage($node);

		$this->currentClassCoverage->addMethodCoverage($this->currentMethodCoverage);

		$this->createMethodEntryCoverageBuilder($this->currentMethodCoverage, $node);
	}

	protected function createMethodEntryCoverageBuilder(MethodCoverage $methodCoverage,
	                                                    Node\Stmt\ClassMethod $methodNode) {
		if (count($methodNode->stmts) == 0) {
			return;
		}

		$entryBuilder = new MethodEntryCoverageBuilder($methodCoverage, $this->logger);
		$this->eventDispatcher->addSubscriber($entryBuilder);
		$this->logger->debug('Added coverage builder for method entry of method ' . $methodNode->name);
	}

	public function methodLeftHandler(SyntaxTreeIteratorEvent $event) {
		$this->currentMethodCoverage = NULL;
	}

	public function nodeHandled(SyntaxTreeIteratorEvent $event) {
		$syntaxTreeNode = $event->getIterator()->current();

		if (!$syntaxTreeNode instanceof Node\Stmt) {
			return;
		}
		if ($syntaxTreeNode instanceof Node\Stmt\If_) {
			$builder = $this->createBuilderForNode($syntaxTreeNode->cond);
			if (isset($this->currentMethodCoverage)) {
				$this->currentMethodCoverage->addInputCoverage($builder->getCoverage());
			} else {
				$this->currentFileCoverage->addCoverage($builder->getCoverage());
			}
		}
	}

	/**
	 * @param Node $node
	 * @return CoverageBuilder
	 */
	protected function createBuilderForNode(Node $node) {
		$builder = $this->builderFactory->createBuilderForExpression($node);

		return $builder;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'syntaxtree.file.entered' => 'fileEnteredHandler',
			//'syntaxtree.file.left' => 'fileLeftHandler',
			'syntaxtree.class.entered' => 'classEnteredHandler',
			'syntaxtree.class.left' => 'classLeftHandler',
			'syntaxtree.classmethod.entered' => 'methodEnteredHandler',
			'syntaxtree.classmethod.left' => 'methodLeftHandler',
		);
	}


}

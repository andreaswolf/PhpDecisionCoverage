<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageBuilderEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\DataSampleEvent;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 *
 * TODO make this a generic coverage builder (i.e. rename it and use CoverageFactory)
 * @author Andreas Wolf <aw@foundata.net>
 */
class SingleConditionCoverageBuilder implements EventSubscriberInterface, CoverageBuilder {

	/**
	 * @var Node\Expr
	 */
	protected $expression;

	/**
	 * @var LoggerInterface
	 */
	protected $log;

	/**
	 * @var SingleConditionCoverage
	 */
	protected $coverage;

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;


	/**
	 * @param Node\Expr $expression The node this builder should generate the coverage for
	 * @param Coverage $coverage
	 * @param EventDispatcherInterface $eventDispatcher
	 */
	public function __construct(Node\Expr $expression, Coverage $coverage, EventDispatcherInterface $eventDispatcher, LoggerInterface $log = NULL) {
		$this->expression = $expression;
		$this->coverage = $coverage;

		$this->log = ($log !== NULL) ? $log : new NullLogger();
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @return SingleConditionCoverage
	 */
	public function getCoverage() {
		return $this->coverage;
	}

	/**
	 * Called for each data sample.
	 *
	 * @param DataSample $sample
	 * @return void
	 */
	protected function handleSample(DataSample $sample) {
		if (!$sample->hasValueFor($this->expression)) {
			return;
		}

		$value = $sample->getValueFor($this->expression);
		$this->coverage->recordCoveredValue($value);
		$this->eventDispatcher->dispatch('coverage.builder.part.covered', new CoverageBuilderEvent($this));
	}

	/**
	 * @param DataSampleEvent $event
	 */
	public function dataSampleReceivedHandler(DataSampleEvent $event) {
		$this->handleSample($event->getDataSample());
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
			'coverage.sample.received' => 'dataSampleReceivedHandler',
		);
	}

}

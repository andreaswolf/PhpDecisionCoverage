<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\Event\DataSampleEvent;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use PhpParser\Node;
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
	 * @var SingleConditionCoverage
	 */
	protected $coverage;


	/**
	 * @param Node\Expr $expression The node this builder should generate the coverage for
	 * @param Coverage $coverage
	 */
	public function __construct(Node\Expr $expression, Coverage $coverage) {
		$this->expression = $expression;

		$this->coverage = $coverage;
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
		// TODO this is generic code -> move it to a generic handler
		if (!$sample->hasValueFor($this->expression)) {
			return;
		}

		$value = $sample->getValueFor($this->expression);
		$this->coverage->recordCoveredValue($value);
	}

	/**
	 * @param DataSampleEvent $event
	 */
	public function dataSampleReceivedHandler(DataSampleEvent $event) {
		if (!$event->getDataSample()->hasValueFor($this->expression)) {
			return;
		}

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
			'coverage.datasample.received' => 'dataSampleReceivedHandler',
		);
	}

}

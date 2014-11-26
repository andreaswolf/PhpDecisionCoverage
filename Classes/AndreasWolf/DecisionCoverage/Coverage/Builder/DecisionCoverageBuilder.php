<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\DataSampleEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class DecisionCoverageBuilder implements EventSubscriberInterface, CoverageBuilder {

	/**
	 * @var LoggerInterface
	 */
	protected $logger;


	/**
	 * @param SingleConditionCoverageBuilder[] $conditionBuilders
	 * @param LoggerInterface $log
	 */
	public function __construct($conditionBuilders, LoggerInterface $log = NULL) {
		if (!$log) {
			$log = new NullLogger();
		}

		$this->logger = $log;
	}

	/**
	 * @param CoverageEvent $event
	 */
	public function conditionCoveredHandler(CoverageEvent $event) {
		// TODO check if all parts of decision are now covered, raise decisioncovered event then
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
			'coveragedata.sample.conditioncovered' => 'conditionCoveredHandler',
		);
	}

}

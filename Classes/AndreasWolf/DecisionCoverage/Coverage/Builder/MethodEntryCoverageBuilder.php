<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\Event\SampleEvent;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\InvocationSample;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Builder for method entry point coverage.
 *
 * Listens for
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class MethodEntryCoverageBuilder implements EventSubscriberInterface, CoverageBuilder {

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var MethodCoverage
	 */
	protected $coverage;


	public function __construct(MethodCoverage $coverage, LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}

		$this->coverage = $coverage;
		$this->logger = $logger;
	}

	/**
	 * Returns the coverage object this builder is building.
	 *
	 * @return Coverage
	 */
	public function getCoverage() {
		return $this->coverage;
	}

	public function sampleHandler(SampleEvent $event) {
		if (!$event->getSample() instanceof InvocationSample) {
			return;
		}
		$this->logger->debug('Invocation sample for method entry encountered');

		$this->coverage->recordMethodEntry();
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'coverage.sample.received' => 'sampleHandler'
		);
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageBuilderEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\DataSampleEvent;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use PhpParser\Node\Expr;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class DecisionCoverageBuilder implements EventSubscriberInterface, CoverageBuilder {

	/**
	 * @var LoggerInterface
	 */
	protected $log;

	/**
	 * @var DecisionCoverage
	 */
	protected $coverage;

	/**
	 * The builders for the parts of this decision.
	 *
	 * This will only be those directly "related" to the decision, e.g. for the "&&" in "A && (B || C)", it will be
	 * A and the ||; the other parts of
	 *
	 * @var CoverageBuilder
	 */
	protected $decisionPartBuilders;

	/**
	 * All builders that have already been covered for the current sample
	 *
	 * @var array
	 */
	protected $coveredBuilders = array();


	/**
	 * @param DecisionCoverage $coverage
	 * @param CoverageBuilder[] $partBuilders
	 * @param LoggerInterface $log
	 */
	public function __construct(DecisionCoverage $coverage, $partBuilders, LoggerInterface $log = NULL) {
		$this->log = ($log !== NULL) ? $log : new NullLogger();

		$this->coverage = $coverage;
		$this->decisionPartBuilders = $partBuilders;
	}

	/**
	 * @return DecisionCoverage
	 */
	public function getCoverage() {
		return $this->coverage;
	}

	public function sampleReceivedHandler(DataSampleEvent $event) {
		/**
		 * for each variable:
		 *   fetch value from data sample
		 *   add it to the internal array
		 * build decision input from fetched variables
		 * add sample to decision coverage
		 */
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
			'coverage.sample.received' => array('sampleReceivedHandler', 0),
		);
	}

}

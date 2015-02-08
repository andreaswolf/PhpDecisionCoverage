<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\Evaluation\DecisionEvaluationDirector;
use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageBuilderEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\CoverageEvent;
use AndreasWolf\DecisionCoverage\Coverage\Event\SampleEvent;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
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

	public function sampleReceivedHandler(SampleEvent $event) {
		// if there was more than one probe for the line our expression was in, we might also get samples of other types
		$sample = $event->getSample();
		if (!$sample instanceof DataSample) {
			return;
		}
		// TODO simplify the expression check -> make DataSample aware of the watched expressions.
		if (!in_array($this->coverage->getExpression(), $sample->getProbe()->getWatchedExpressions())) {
			return;
		}

		// TODO create in constructor and add as an object property
		$inputSampleBuilder = new DecisionEvaluationDirector($this->coverage->getExpression());

		try {
			$decisionSample = $inputSampleBuilder->evaluate($sample);
		} catch (\Exception $e) {
			throw new \RuntimeException('Could not evaluate input sample for decision '
				. $this->coverage->getExpression()->getAttribute('coverage__nodeId'), 0, $e);
		}

		$this->coverage->addSample($decisionSample);
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

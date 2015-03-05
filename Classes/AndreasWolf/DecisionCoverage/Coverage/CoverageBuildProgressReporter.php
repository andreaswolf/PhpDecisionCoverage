<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DecisionCoverage\Coverage\Event\SampleEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class CoverageBuildProgressReporter implements EventSubscriberInterface {

	/**
	 * @var OutputInterface
	 */
	protected $output;

	protected $currentSample = 0;


	public function __construct(OutputInterface $output) {
		$this->output = $output;
	}

	public function sampleReceivedHandler(SampleEvent $event) {
		++$this->currentSample;

		if ($this->currentSample % 100 == 0) {
			$output = ".";

			if ($this->currentSample % 2500 == 0) {
				$output .= " ";
			}
			$this->output->write($output);
		}
		if ($this->currentSample % 5000 == 0) {
			$this->output->writeln(" [{$this->currentSample}]");
		}
	}

	public function buildFinishedHandler() {
		// Finish previous line
		$this->output->writeln('');

		$this->output->writeln(sprintf(
			'<info>Distributed %d samples for coverage building</info>',
			$this->currentSample
		));
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'coverage.sample.received' => 'sampleReceivedHandler',
			'coverage.build.end' => 'buildFinishedHandler',
		);
	}

}

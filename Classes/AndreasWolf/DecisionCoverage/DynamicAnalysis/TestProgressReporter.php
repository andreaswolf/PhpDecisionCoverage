<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis;

use AndreasWolf\DecisionCoverage\Event\TestEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class TestProgressReporter implements EventSubscriberInterface {

	/**
	 * @var OutputInterface
	 */
	protected $output;


	public function __construct(OutputInterface $output) {
		$this->output = $output;
	}

	public function testStartHandler(TestEvent $event) {
		$this->output->write('  Test ' . $event->getTest());
	}

	public function testEndHandler(TestEvent $event) {
		$this->output->writeln(" ✔");
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'test.start' => 'testStartHandler',
			'test.end' => 'testEndHandler'
		);
	}

}

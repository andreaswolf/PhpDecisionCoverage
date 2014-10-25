<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\Event\TestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Handles events that occur during a test run.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TestEventHandler implements EventSubscriberInterface {

	/**
	 * @var CoverageDataSet
	 */
	protected $coverageDataSet;


	/**
	 * @param CoverageDataSet $dataSet
	 */
	public function __construct(CoverageDataSet $dataSet) {
		$this->coverageDataSet = $dataSet;
	}

	/**
	 * @param TestEvent $event
	 */
	public function testStartHandler(TestEvent $event) {
		$this->coverageDataSet->enterTest($event->getTest());
	}

	/**
	 * @param TestEvent $event
	 */
	public function testEndHandler(TestEvent $event) {
		$this->coverageDataSet->exitTest($event->getTest());
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
			'test.start' => 'testStartHandler',
			'test.end' => 'testEndHandler',
		);
	}

}

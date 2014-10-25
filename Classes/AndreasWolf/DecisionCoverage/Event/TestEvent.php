<?php
namespace AndreasWolf\DecisionCoverage\Event;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\Test;
use Symfony\Component\EventDispatcher\Event;


/**
 * All sorts of events related to running a test
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TestEvent extends Event {

	/**
	 * @var Test
	 */
	protected $test;

	public function __construct(Test $test) {
		$this->test = $test;
	}

	/**
	 * @return Test
	 */
	public function getTest() {
		return $this->test;
	}

}

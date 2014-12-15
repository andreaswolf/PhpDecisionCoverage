<?php
namespace AndreasWolf\DecisionCoverage\Event;

use Symfony\Component\EventDispatcher\Event;


class IteratorEvent extends Event {

	/**
	 * @var \RecursiveIteratorIterator
	 */
	protected $iterator;


	public function __construct(\Iterator $iterator) {
		$this->iterator = $iterator;
	}

	/**
	 * @return \RecursiveIteratorIterator
	 */
	public function getIterator() {
		return $this->iterator;
	}

}

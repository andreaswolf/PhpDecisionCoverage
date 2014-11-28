<?php
namespace AndreasWolf\DecisionCoverage\Event;

use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use Symfony\Component\EventDispatcher\Event;


class SyntaxTreeIteratorEvent extends Event {

	/**
	 * @var SyntaxTreeIterator
	 */
	protected $iterator;


	public function __construct(SyntaxTreeIterator $iterator) {
		$this->iterator = $iterator;
	}

	/**
	 * @return SyntaxTreeIterator
	 */
	public function getIterator() {
		return $this->iterator;
	}

}

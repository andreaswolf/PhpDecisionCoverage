<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Event;

use AndreasWolf\DecisionCoverage\Coverage\Builder\CoverageBuilder;
use Symfony\Component\EventDispatcher\Event;


class CoverageBuilderEvent extends Event {

	/**
	 * @var CoverageBuilder
	 */
	protected $builder;


	public function __construct(CoverageBuilder $builder) {
		$this->builder = $builder;
	}

	/**
	 * @return CoverageBuilder
	 */
	public function getBuilder() {
		return $this->builder;
	}

}
 
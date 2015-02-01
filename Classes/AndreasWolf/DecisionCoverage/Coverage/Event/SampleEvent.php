<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Event;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\Sample;
use Symfony\Component\EventDispatcher\Event;


class SampleEvent extends Event {

	/**
	 * @var Sample
	 */
	protected $dataSample;


	public function __construct(Sample $sample) {
		$this->dataSample = $sample;
	}

	/**
	 * @return Sample
	 */
	public function getSample() {
		return $this->dataSample;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Event;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use Symfony\Component\EventDispatcher\Event;


class DataSampleEvent extends Event {

	/**
	 * @var DataSample
	 */
	protected $dataSample;


	public function __construct(DataSample $sample) {
		$this->dataSample = $sample;
	}

	/**
	 * @return DataSample
	 */
	public function getDataSample() {
		return $this->dataSample;
	}

}

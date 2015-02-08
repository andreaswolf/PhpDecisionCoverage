<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Event;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use Symfony\Component\EventDispatcher\Event;


class CoverageDataSetEvent extends Event {

	/**
	 * @var CoverageDataSet
	 */
	protected $dataSet;


	public function __construct(CoverageDataSet $dataSet) {
		$this->dataSet = $dataSet;
	}

	/**
	 * @return CoverageDataSet
	 */
	public function getDataSet() {
		return $this->dataSet;
	}

}

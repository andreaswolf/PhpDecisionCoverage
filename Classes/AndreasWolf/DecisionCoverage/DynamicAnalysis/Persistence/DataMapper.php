<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Persistence;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;


interface DataMapper {

	/**
	 * @param $filePath
	 * @return CoverageDataSet
	 */
	public function readFromFile($filePath);

	/**
	 * @param string $filePath
	 * @param CoverageDataSet $dataSet
	 *
	 * @throws \RuntimeException If writing the file failed
	 */
	public function writeToFile($filePath, CoverageDataSet $dataSet);

}

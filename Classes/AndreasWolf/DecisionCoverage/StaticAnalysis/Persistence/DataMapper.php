<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\Persistence;

use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;


interface DataMapper {

	/**
	 * @param string $filePath
	 * @return ResultSet
	 */
	public function loadFromFile($filePath);

	/**
	 * @param string $filePath
	 * @param ResultSet $result
	 * @return bool TRUE if saving succeeded
	 *
	 * @throws \RuntimeException If saving the data failed
	 */
	public function saveToFile($filePath, ResultSet $result);

}

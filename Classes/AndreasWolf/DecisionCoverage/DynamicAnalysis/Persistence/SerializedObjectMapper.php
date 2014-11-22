<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Persistence;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SerializedObjectMapper implements DataMapper {

	/**
	 * @param string $filePath
	 * @return CoverageDataSet
	 */
	public function readFromFile($filePath) {
		$fileContents = file_get_contents($filePath);
		$object = unserialize($fileContents);

		return $object;
	}

	/**
	 * @param string $filePath
	 * @param CoverageDataSet $dataSet
	 *
	 * @throws \RuntimeException If writing the file failed
	 */
	public function writeToFile($filePath, CoverageDataSet $dataSet) {
		$result = file_put_contents($filePath, serialize($dataSet));

		if ($result === FALSE) {
			throw new \RuntimeException('Could not write results to file', 1413653932);
		}
	}

}

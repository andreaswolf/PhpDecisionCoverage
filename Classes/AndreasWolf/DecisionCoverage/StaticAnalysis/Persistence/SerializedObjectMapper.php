<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\Persistence;

use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;


/**
 */
class SerializedObjectMapper implements DataMapper {

	/**
	 * @param string $filePath
	 * @return ResultSet
	 */
	public function loadFromFile($filePath) {
		$fileContents = file_get_contents($filePath);

		$object = unserialize($fileContents);

		return $object;
	}

	/**
	 * @param string $filePath
	 * @param ResultSet $result
	 * @return bool TRUE if saving succeeded
	 *
	 * @throws \RuntimeException If saving the data failed
	 */
	public function saveToFile($filePath, ResultSet $result) {
		$result = file_put_contents($filePath, $result->serialize());

		if ($result === FALSE) {
			throw new \RuntimeException('Could not write results to file', 1413653932);
		}
	}

}

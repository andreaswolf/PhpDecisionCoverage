<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;


/**
 * A collection of analysis results of files.
 */
class ResultSet {

	/**
	 * The results, with the file path as key.
	 *
	 * @var FileResult[]
	 */
	protected $fileResults;


	/**
	 * @param FileResult $result
	 * @return void
	 */
	public function addFileResult(FileResult $result) {
		$this->fileResults[$result->getFilePath()] = $result;
	}

	/**
	 * @param string $path
	 * @return FileResult|null
	 */
	public function getResultForPath($path) {
		if (!isset($this->fileResults[$path])) {
			return NULL;
		}

		return $this->fileResults[$path];
	}

	/**
	 * @return FileResult[]
	 */
	public function getFileResults() {
		return $this->fileResults;
	}

	/**
	 * Serializes this object and returns the representation.
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize($this);
	}

}

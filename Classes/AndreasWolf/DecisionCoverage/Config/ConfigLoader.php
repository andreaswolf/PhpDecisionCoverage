<?php
namespace AndreasWolf\DecisionCoverage\Config;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMException;


class ConfigLoader {

	/**
	 * @param $fileName
	 * @return ApplicationConfig
	 * @throws ConfigLoaderException
	 * @throws \TheSeer\fDOM\fDOMException
	 */
	public function load($fileName) {
		if (!file_exists($fileName)) {
			throw new ConfigLoaderException('File not found', ConfigLoaderException::NOT_FOUND);
		}
		$oldDir = getcwd();
		chdir(dirname($fileName));

		try {
			$document = new fDOMDocument();
			$document->load($fileName);

			if ($document->documentElement->localName != 'decision-coverage') {
				throw new ConfigLoaderException('Invalid namespace', ConfigLoaderException::INVALID_NAMESPACE);
			}

			$configObject = new ApplicationConfig($document);
		} catch (fDOMException $e) {
			throw new ConfigLoaderException('Error while parsing config file: ' . $e->getMessage(),
				ConfigLoaderException::PARSE_ERROR);
		}

		return $configObject;
	}

}

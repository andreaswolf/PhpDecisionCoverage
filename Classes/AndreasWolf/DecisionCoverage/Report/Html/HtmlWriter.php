<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;

use AndreasWolf\DecisionCoverage\Report\Writer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use TheSeer\fDOM\fDOMDocument;
use TheSeer\fXSL\fXSLTProcessor;


class HtmlWriter implements Writer {

	/**
	 * @var string
	 */
	protected $basePath;

	/**
	 * @var string
	 */
	protected $fileExtension = '.html';

	/**
	 * @var LoggerInterface
	 */
	protected $logger;


	public function __construct($basePath, LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}
		$this->logger = $logger;

		$this->basePath = rtrim($basePath, '/') . '/';
	}

	public function writeReportForSourceFile(SourceFile $file) {
		$xmlFileBuilder = new ReportFileXmlBuilder($this->logger);

		$lineNumber = 0;
		foreach ($file->getLines() as $line) {
			++$lineNumber;

			$xmlFileBuilder->createNodeForLine($line, $lineNumber);
		}
		$xmlFileBuilder->createCoverageNodes($file->getCoverages());

		$xslSource = new fDOMDocument();
		$xslSource->load(__DIR__ . '/../../../../../Resources/Templates/Html/SourceFile.xsl');
		$xslProcessor = new fXSLTProcessor($xslSource);

		$xmlDocument = $xmlFileBuilder->build();

		$reportFile = $this->basePath . $this->getReportTargetFilename($file);
		file_put_contents($reportFile . '.xml', $xmlDocument->saveXML());

		$contents = $xslProcessor->transformToXml($xmlDocument);
		file_put_contents($reportFile, $contents);
	}

	protected function getReportTargetFilename(SourceFile $sourceFile) {
		$filePath = $sourceFile->getPath();

		$filePath = substr($filePath, 1);
		$filePath = str_replace(DIRECTORY_SEPARATOR, '_', $filePath);

		$filePath .= $this->fileExtension;

		return $filePath;
	}

}

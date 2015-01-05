<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;

use AndreasWolf\DecisionCoverage\Report\Annotation\DecisionCoverageAnnotation;
use AndreasWolf\DecisionCoverage\Report\Writer;
use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;
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
	 * @var fDOMDocument
	 */
	protected $document;

	/**
	 * @var fDOMElement
	 */
	protected $linesNode;


	public function __construct($basePath) {
		$this->basePath = rtrim($basePath, '/') . '/';
	}

	public function writeReportForSourceFile(SourceFile $file) {
		$this->document = new fDOMDocument();
		$this->createLinesNode();
		$lineNumber = 0;
		foreach ($file->getLines() as $line) {
			++$lineNumber;

			$this->createNodeForLine($line, $lineNumber);
		}

		$xslSource = new fDOMDocument();
		$xslSource->load(__DIR__ . '/../../../../../Resources/Templates/Html/SourceFile.xsl');
		$xslProcessor = new fXSLTProcessor($xslSource);

		$contents = $xslProcessor->transformToXml($this->document);
		$reportFile = $this->basePath . $this->getReportTargetFilename($file);
		file_put_contents($reportFile, $contents);
	}

	protected function createLinesNode() {
		$sourcesNode = $this->document->createElement('source');
		$sourcesNode->setAttribute('file', 'FIXMEsomeFile.php');
		$this->linesNode = $sourcesNode->createElement('lines');
		$sourcesNode->appendChild($this->linesNode);
		$this->document->appendChild($sourcesNode);
	}

	/**
	 * @param SourceLine $line
	 * @param int $lineNumber
	 * @throws \TheSeer\fDOM\fDOMException
	 */
	protected function createNodeForLine($line, $lineNumber) {
		$lineNode = $this->linesNode->createElement('line', NULL, TRUE);
		$lineNode->setAttribute('number', $lineNumber);

		$rawLineContents = $line->getContents();
		if (!$line->hasAnnotations()) {
			$rawLineContents = $this->prepareCodeForHtmlFile($rawLineContents);
			$lineContentsNode = $this->document->createCDATASection($rawLineContents);
			$lineNode->appendChild($lineContentsNode);
		} else {
			// TODO we now assume that the annotations are not overlapping; this might not always be the case
			$startOffset = $line->getOffset();
			$annotations = $line->getAnnotations();
			// make sure the annotations are ordered by their start offsets
			usort($annotations, function($left, $right) {
				return $right['startOffset'] - $left['startOffset'];
			});

			$lastOffset = $startOffset;
			/** @var DecisionCoverageAnnotation $annotation */
			foreach ($annotations as $annotation) {
				$currentOffset = $annotation['start'];
				if ($lastOffset < $currentOffset) {
					// add fragment for the space in between the last and this element
					$fragmentNode = $this->createFragmentNode(
						substr($rawLineContents,
							$lastOffset - $startOffset,
							$currentOffset - $lastOffset
						)
					);
					$lineNode->appendChild($fragmentNode);
				}

				$fragmentNode = $this->createFragmentNode(
					substr($rawLineContents,
						$currentOffset - $startOffset,
						$annotation['end'] - $currentOffset + 1
					)
				);
				$lineNode->appendChild($fragmentNode);

				$lastOffset = $annotation['end'] + 1;
			}

			$fragmentNode = $this->createFragmentNode(substr($rawLineContents, $lastOffset - $startOffset));
			$lineNode->appendChild($fragmentNode);
		}

		$this->linesNode->appendChild($lineNode);
	}

	protected function createFragmentNode($contents) {
		$contentsNode = $this->document->createCDATASection($this->prepareCodeForHtmlFile($contents));
		$fragmentNode = $this->linesNode->createElement('fragment');
		$fragmentNode->appendChild($contentsNode);

		return $fragmentNode;
	}

	protected function getReportTargetFilename(SourceFile $sourceFile) {
		$filePath = $sourceFile->getPath();

		$filePath = substr($filePath, 1);
		$filePath = str_replace(DIRECTORY_SEPARATOR, '_', $filePath);

		$filePath .= $this->fileExtension;

		return $filePath;
	}

	/**
	 * @param string $contents
	 * @return string
	 */
	protected function prepareCodeForHtmlFile($contents) {
		$contents = str_replace("\t", "    ", $contents);

		return $contents;
	}

}

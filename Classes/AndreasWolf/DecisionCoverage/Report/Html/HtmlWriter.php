<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;


use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;
use TheSeer\fXSL\fXSLTProcessor;


class HtmlWriter {

	/**
	 * @var fDOMDocument
	 */
	protected $document;

	/**
	 * @var fDOMElement
	 */
	protected $linesNode;

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

		echo $xslProcessor->transformToXml($this->document);
	}

	/**
	 * @param fDOMDocument $document
	 */
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
		$rawLineContents = $line->getContents();
		$rawLineContents = str_replace("\t", "»   ", $rawLineContents);
		$lineContentsNode = $this->document->createCDATASection($rawLineContents);
		$lineNode = $this->linesNode->createElement('line', NULL, TRUE);
		$lineNode->appendChild($lineContentsNode);
		$lineNode->setAttribute('number', $lineNumber);

		$this->linesNode->appendChild($lineNode);
	}

}

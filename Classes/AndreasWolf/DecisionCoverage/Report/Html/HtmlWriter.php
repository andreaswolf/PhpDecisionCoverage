<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\CoverageAggregate;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
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

	/**
	 * @var fDOMElement
	 */
	protected $coverageNode;


	public function __construct($basePath) {
		$this->basePath = rtrim($basePath, '/') . '/';
	}

	public function writeReportForSourceFile(SourceFile $file) {
		$this->document = new fDOMDocument();
		$this->createTopLevelNodes();
		$lineNumber = 0;
		foreach ($file->getLines() as $line) {
			++$lineNumber;

			$this->createNodeForLine($line, $lineNumber);
		}
		$this->createCoverageNodes($file->getCoverages());

		$xslSource = new fDOMDocument();
		$xslSource->load(__DIR__ . '/../../../../../Resources/Templates/Html/SourceFile.xsl');
		$xslProcessor = new fXSLTProcessor($xslSource);

		$contents = $xslProcessor->transformToXml($this->document);
		$reportFile = $this->basePath . $this->getReportTargetFilename($file);
		file_put_contents($reportFile, $contents);
	}

	/**
	 * Creates the basic document structure, namely the document root node and the group nodes for lines and additional
	 * metadata.
	 *
	 * @throws \TheSeer\fDOM\fDOMException
	 */
	protected function createTopLevelNodes() {
		$sourcesNode = $this->document->createElement('source');
		$sourcesNode->setAttribute('file', 'FIXMEsomeFile.php');
		$this->document->appendChild($sourcesNode);

		$this->linesNode = $sourcesNode->createElement('lines');
		$sourcesNode->appendChild($this->linesNode);

		$this->coverageNode = $sourcesNode->createElement('coverages');
		$sourcesNode->appendChild($this->coverageNode);
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
					),
					$annotation['annotation']
				);
				$lineNode->appendChild($fragmentNode);

				$lastOffset = $annotation['end'] + 1;
			}

			$fragmentNode = $this->createFragmentNode(substr($rawLineContents, $lastOffset - $startOffset));
			$lineNode->appendChild($fragmentNode);
		}

		$this->linesNode->appendChild($lineNode);
	}

	/**
	 * Adds coverage nodes for the given coverage(s).
	 *
	 * Currently, this method only supports decision coverages.
	 *
	 * @param Coverage[] $coverages
	 */
	protected function createCoverageNodes($coverages) {
		foreach ($coverages as $coverage) {
			if ($coverage instanceof CoverageAggregate) {
				$this->createCoverageNodes($coverage->getCoverages());

				continue;
			} elseif ($coverage instanceof DecisionCoverage) {
				$coverageNode = $this->coverageNode->appendElement('coverage');
				$coverageNode->setAttribute('type', 'decision');
				$coverageNode->setAttribute('id', $coverage->getId());

				$inputsNodes = $coverageNode->appendElement('inputs');

				foreach ($coverage->getFeasibleInputs() as $input) {
					$inputNode = $inputsNodes->appendElement('input');
					$inputNode->setAttribute('covered', $coverage->isCovered($input) ? 'true' : 'false');

					// TODO add the tests covering this input as subnodes
				}
			} else {
				throw new \RuntimeException('Unsupported coverage type ' . get_class($coverage));
			}
		}
	}

	/**
	 * Creates a node for the given fragment contents.
	 *
	 * If an annotation is given, the contents are wrapped in a <contents> child with the <annotation> node as a
	 * sibling.
	 *
	 * @param string $contents
	 * @param null $annotation
	 * @return fDOMElement
	 * @throws \TheSeer\fDOM\fDOMException
	 */
	protected function createFragmentNode($contents, $annotation = NULL) {
		$fragmentNode = $this->linesNode->createElement('fragment');
		$fragmentContentNode = $this->document->createCDATASection($this->prepareCodeForHtmlFile($contents));

		if (is_object($annotation)) {
			if ($annotation instanceof DecisionCoverageAnnotation) {
				$coverage = $annotation->getCoverage();
				$annotationNode = $fragmentNode->appendElement('annotation');
				$annotationNode->setAttribute('type', 'coverage');
				$annotationNode->setAttribute('coverage', $coverage->getId());

				$contentsNode = $fragmentNode->appendElement('contents');
				$contentsNode->appendChild($fragmentContentNode);
			}
		} else {
			$fragmentNode->appendChild($fragmentContentNode);
		}

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

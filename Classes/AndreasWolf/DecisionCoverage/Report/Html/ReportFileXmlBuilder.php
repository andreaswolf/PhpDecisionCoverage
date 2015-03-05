<?php
namespace AndreasWolf\DecisionCoverage\Report\Html;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\CoverageAggregate;
use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\Report\Annotation\ClassCoverageAnnotation;
use AndreasWolf\DecisionCoverage\Report\Annotation\InputCoverageAnnotation;
use AndreasWolf\DecisionCoverage\Report\Annotation\MethodCoverageAnnotation;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;


/**
 * Builder for a single source file coverage report.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ReportFileXmlBuilder {

	/** @var string */
	protected $filename;

	/** @var fDOMDocument */
	protected $document;

	/** @var fDOMElement */
	protected $linesNode;

	/** @var fDOMElement */
	protected $coverageNode;

	/** @var LoggerInterface */
	protected $logger;


	public function __construct(LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}
		$this->logger = $logger;

		$this->document = new fDOMDocument();
		$this->document->formatOutput = TRUE;

		$this->createTopLevelNodes();
	}

	public function setSourceFilename($filename) {
		$this->filename = $filename;
	}

	/**
	 * Builds and returns the XML document node.
	 *
	 * @return fDOMDocument
	 */
	public function build() {
		return $this->document;
	}

	/**
	 * Creates the basic document structure, namely the document root node and the group nodes for lines and additional
	 * metadata.
	 *
	 * @throws \TheSeer\fDOM\fDOMException
	 */
	protected function createTopLevelNodes() {
		$sourcesNode = $this->document->appendElement('source');
		$sourcesNode->setAttribute('file', 'FIXMEsomeFile.php');

		$this->linesNode = $sourcesNode->appendElement('lines');

		$this->coverageNode = $sourcesNode->appendElement('coverages');
	}

	/**
	 * @param SourceLine $line
	 * @param int $lineNumber
	 * @throws \TheSeer\fDOM\fDOMException
	 */
	public function createNodeForLine($line, $lineNumber) {
		$lineNode = $this->linesNode->appendElement('line', NULL);
		$lineNode->setAttribute('number', $lineNumber);

		$rawLineContents = $line->getContents();
		if (!$line->hasAnnotations()) {
			$rawLineContents = $this->prepareCodeForHtmlFile($rawLineContents);
			$lineContentsNode = $this->document->createCDATASection($rawLineContents);
			$lineNode->appendChild($lineContentsNode);
		} else {
			// TODO we now assume that the annotations are not overlapping; this might not always be the case
			$this->createFragmentNodesForLine($line, $lineNode, $rawLineContents);
		}
	}

	/**
	 * Adds coverage nodes for the given coverage(s).
	 *
	 * Currently, this method only supports decision coverages.
	 *
	 * @param Coverage[] $coverages
	 */
	public function createCoverageNodes($coverages) {
		foreach ($coverages as $coverage) {
			$this->logger->debug('Creating coverage node for coverage ' . $coverage->getId()
				. ' (type ' . get_class($coverage) . ')');
			if ($coverage instanceof CoverageAggregate) {
				$this->createCoverageNodesForAggregate($coverage);
			} elseif ($coverage instanceof DecisionCoverage) {
				// TODO unify this with the handler for single conditions
				$coverageNode = $this->coverageNode->appendElement('coverage');
				$coverageNode->setAttribute('type', 'decision');
				$coverageNode->setAttribute('id', $coverage->getId());
				$coverageNode->setAttribute('inputCoverage', $coverage->getCoverage());

				$inputsNode = $coverageNode->appendElement('inputs');

				foreach ($coverage->getFeasibleInputs() as $input) {
					$inputNode = $inputsNode->appendElement('input');
					$inputNode->setAttribute('covered', $coverage->isCovered($input) ? 'true' : 'false');

					foreach ($coverage->getSamplesForInput($input) as $sample) {
						$coveredByNode = $inputNode->appendElement('covered-by');
						$coveredByNode->setAttribute('test', $sample->getTest());
					}
				}
			} elseif ($coverage instanceof SingleConditionCoverage) {
				$coverageNode = $this->coverageNode->appendElement('coverage');
				$coverageNode->setAttribute('type', 'condition');
				$coverageNode->setAttribute('id', $coverage->getId());
				$coverageNode->setAttribute('inputCoverage', $coverage->getCoverage());

				$inputsNode = $coverageNode->appendElement('inputs');

				$inputNode = $inputsNode->appendElement('input');
				$inputNode->setAttribute('value', 'true');
				$inputNode->setAttribute('covered', $coverage->isValueCovered(TRUE) ? 'true' : 'false');
				$inputNode = $inputsNode->appendElement('input');
				$inputNode->setAttribute('value', 'false');
				$inputNode->setAttribute('covered', $coverage->isValueCovered(FALSE) ? 'true' : 'false');
			} else {
				// ignore unknown types like SingleConditionCoverage for now
				//throw new \RuntimeException('Unsupported coverage type ' . get_class($coverage));
			}
		}
	}

	protected function createCoverageNodesForAggregate(CoverageAggregate $coverage) {
		// no need to trigger coverage generation for the aggregated coverages here, as the hierarchy has been flattened
		// out for the SourceFile instance by Generator

		if ($coverage instanceof MethodCoverage) {
			$coverageNode = $this->coverageNode->appendElement('coverage');
			$coverageNode->setAttribute('type', 'method');
			$coverageNode->setAttribute('id', $coverage->getId());
			$coverageNode->setAttribute('method', $coverage->getMethodName());
			$coverageNode->setAttribute('feasibleDecisionInputs', $coverage->countFeasibleDecisionInputs());
			$coverageNode->setAttribute('coveredDecisionInputs', $coverage->countCoveredDecisionInputs());
			$coverageNode->setAttribute('inputCoverage', $coverage->getDecisionCoverage());

			$entryPointNode = $coverageNode->appendElement('entry-point');
			$entryPointNode->setAttribute('covered', $coverage->getEntryPointCoverage() === 1.0 ? 'true' : 'false');
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
			switch (TRUE) {
				case $annotation instanceof InputCoverageAnnotation:
				case $annotation instanceof MethodCoverageAnnotation:
				case $annotation instanceof ClassCoverageAnnotation:

					$coverage = $annotation->getCoverage();
					$annotationNode = $fragmentNode->appendElement('annotation');
					$annotationNode->setAttribute('type', 'coverage');
					$annotationNode->setAttribute('coverage', $coverage->getId());

					$contentsNode = $fragmentNode->appendElement('contents');
					$contentsNode->appendChild($fragmentContentNode);
				break;

				default:
					throw new \RuntimeException('Unknown fragment annotation type ' . get_class($annotation));
			}
		} else {
			$fragmentNode->appendChild($fragmentContentNode);
		}

		return $fragmentNode;
	}

	/**
	 * @param string $contents
	 * @return string
	 */
	protected function prepareCodeForHtmlFile($contents) {
		$contents = str_replace("\t", "    ", $contents);

		return $contents;
	}

	/**
	 * @param $line
	 * @param $lineNode
	 * @param $rawLineContents
	 */
	protected function createFragmentNodesForLine(SourceLine $line, fDOMElement $lineNode, $rawLineContents) {
		$startOffset = $line->getOffset();
		$annotations = $line->getAnnotations();

		// make sure the annotations are ordered by their start offsets
		usort($annotations, function ($left, $right) {
			return $right['startOffset'] - $left['startOffset'];
		});

		$lastOffset = $startOffset;
		/** @var InputCoverageAnnotation $annotation */
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

}

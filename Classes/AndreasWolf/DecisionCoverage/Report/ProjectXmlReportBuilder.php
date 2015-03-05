<?php
namespace AndreasWolf\DecisionCoverage\Report;

use AndreasWolf\DecisionCoverage\Coverage\ClassCoverage;
use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\CoverageAggregate;
use AndreasWolf\DecisionCoverage\Coverage\CoverageSet;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\InputCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Report\Html\SourceFile;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;
use TheSeer\fDOM\fDOMNode;


/**
 * Report builder for the per-project overall XML coverage report.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ProjectXmlReportBuilder implements ReportBuilder {

	/** @var \SplFileInfo */
	protected $basePath;

	/**
	 * The generated XML document.
	 *
	 * @var fDOMDocument
	 */
	protected $document;

	/**
	 * @var fDOMElement
	 */
	protected $rootNode;

	/** @var fDOMElement[] */
	protected $nodeStack;

	/** @var LoggerInterface */
	protected $logger;


	public function __construct($basePath, LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}
		$this->logger = $logger;

		$this->basePath = $basePath;

		$this->prepareDocumentNode();
	}

	protected function prepareDocumentNode() {
		$this->document = new fDOMDocument();
		// create the root node
		$this->rootNode = $this->document->appendElement('coverage');
		$this->document->formatOutput = TRUE;

		$this->stackPush($this->rootNode);
	}

	/**
	 * @return fDOMDocument
	 */
	public function getXmlDocument() {
		return $this->document;
	}

	/**
	 * Builds a report for the given coverage set
	 *
	 * @param CoverageSet $set
	 * @return void
	 */
	public function build(CoverageSet $set) {
		$this->handleCoverageSet($set);
	}


	/**
	 * Writes the report to an XML file in the base path given to this class.
	 *
	 * @throws \TheSeer\fDOM\fDOMException
	 */
	public function finish() {
		$reportFile = $this->basePath->getPathname() . '/' . '_project.xml';
		$contents = $this->document->saveXML();

		file_put_contents($reportFile, $contents);
	}

	/**
	 * @param Coverage|CoverageAggregate $coverage
	 */
	protected function handleCoverage($coverage) {
		switch (TRUE) {
			case $coverage instanceof FileCoverage:
				$node = $this->handleFileCoverage($coverage);
				break;

			case $coverage instanceof ClassCoverage:
				$node = $this->handleClassCoverage($coverage);
				break;

			case $coverage instanceof MethodCoverage:
				$this->handleMethodCoverage($coverage);
				break;
		}
	}

	/**
	 * Returns the currently active coverage node
	 *
	 * @return fDOMElement
	 */
	protected function stackCurrent() {
		return $this->nodeStack[count($this->nodeStack) - 1];
	}

	/**
	 * @param fDOMElement $node
	 * @return void
	 */
	protected function stackPush(fDOMElement $node) {
		$this->nodeStack[] = $node;
	}

	/**
	 * @return fDOMElement
	 */
	protected function stackPop() {
		return array_pop($this->nodeStack);
	}

	protected function handleCoverageSet(CoverageSet $set) {
		$node = $this->stackCurrent()->appendElement('project');

		$this->addInputCoverageNodes($node, $set->countFeasibleDecisionInputs(), $set->countCoveredDecisionInputs());

		$this->handleSubcoveragesOfNode($node, $set->getAll());

		return $node;
	}

	protected function handleFileCoverage(FileCoverage $coverage) {
		$node = $this->stackCurrent()->appendElement('file');
		$node->setAttribute('path', $coverage->getFilePath());

		$this->addInputCoverageNodes($node, $coverage->countFeasibleDecisionInputs(), $coverage->countCoveredDecisionInputs());
		$this->handleSubcoveragesOfNode($node, $coverage->getCoverages());

		return $node;
	}

	protected function handleClassCoverage(ClassCoverage $coverage) {
		$node = $this->stackCurrent()->appendElement('class');
		$node->setAttribute('name', $coverage->getClassName());

		$this->addInputCoverageNodes($node, $coverage->countFeasibleDecisionInputs(), $coverage->countCoveredDecisionInputs());
		$this->handleSubcoveragesOfNode($node, $coverage->getMethodCoverages());

		return $node;
	}

	protected function handleMethodCoverage(MethodCoverage $coverage) {
		$node = $this->stackCurrent()->appendElement('method');
		$node->setAttribute('name', $coverage->getMethodName());

		$this->addInputCoverageNodes($node, $coverage->countFeasibleDecisionInputs(), $coverage->countCoveredDecisionInputs());
	}

	protected function addInputCoverageNodes(fDOMElement $node, $feasibleInputs, $coveredInputs) {
		$inputNode = $node->appendElement('input-coverage');
		$inputNode->setAttribute('feasibleDecisionInputs', $feasibleInputs);
		$inputNode->setAttribute('coveredDecisionInputs', $coveredInputs);
	}

	/**
	 * Takes a list of coverages that are subcoverages of the given node and adds nodes for them inside the node.
	 *
	 * @param fDOMElement $node
	 * @param Coverage[] $coverages
	 */
	protected function handleSubcoveragesOfNode($node, $coverages) {
		$this->stackPush($node);
		foreach ($coverages as $subcoverage) {
			$this->handleCoverage($subcoverage);
		}
		$this->stackPop();
	}

}

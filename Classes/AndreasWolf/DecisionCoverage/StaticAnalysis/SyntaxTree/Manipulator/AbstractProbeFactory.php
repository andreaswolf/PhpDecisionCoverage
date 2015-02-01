<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\NodeVisitor;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


abstract class AbstractProbeFactory implements NodeVisitor {

	/**
	 * @var FileResult
	 */
	protected $analysis;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;


	public function __construct(FileResult $analysis, LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}

		$this->analysis = $analysis;
		$this->logger = $logger;
	}

	/**
	 * Signal for the start of an instrumentation run.
	 *
	 * @param Node[] $rootNodes
	 * @return void
	 */
	public function startInstrumentation($rootNodes) {
	}

	/**
	 * Signal for the end of an instrumentation run.
	 *
	 * @param Node[] $rootNodes
	 * @return void
	 */
	public function endInstrumentation($rootNodes) {
	}

	/**
	 * Attaches the given probe to the coverage__probe attribute of the node and to the analysis result.
	 *
	 * The former relation makes clear where the probe belongs (i.e. to which syntax tree node), the latter is
	 * important because it is used by the dynamic analysis to set the breakpoints and record coverage data.
	 *
	 * @param Node $node
	 * @param Probe $probe
	 */
	protected function attachProbeToNodeAndAnalysis(Node $node, $probe) {
		if ($node->hasAttribute('coverage__probe')) {
			$probes = $node->getAttribute('coverage__probe');
			if (!is_array($probes)) {
				$probes = [$probes, $probe];
			} else {
				$probes[] = $probe;
			}
		} else {
			$probes = [$probe];
		}
		$node->setAttribute('coverage__probe', $probes);

		$this->analysis->addProbe($probe);
	}

}

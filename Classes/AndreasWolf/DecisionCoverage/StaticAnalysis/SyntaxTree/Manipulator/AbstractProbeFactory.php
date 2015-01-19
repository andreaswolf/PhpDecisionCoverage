<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult;
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

}

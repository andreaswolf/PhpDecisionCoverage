<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger;

use AndreasWolf\DebuggerClient\Protocol\Breakpoint\Breakpoint;
use AndreasWolf\DecisionCoverage\StaticAnalysis\DataCollectionProbe;


/**
 * Connects one or more probes to a debugger breakpoint.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ProbeConnector {

	/**
	 * @var Breakpoint
	 */
	protected $breakpoint;

	/**
	 * @var DataCollectionProbe[]
	 */
	protected $probes;

	/**
	 * @param Breakpoint $breakpoint
	 * @param DataCollectionProbe[] $probes
	 */
	public function __construct(Breakpoint $breakpoint, array $probes) {
		$this->breakpoint = $breakpoint;
		$this->probes = $probes;
	}

	/**
	 * @return Breakpoint
	 */
	public function getBreakpoint() {
		return $this->breakpoint;
	}

	/**
	 * @return \AndreasWolf\DecisionCoverage\StaticAnalysis\DataCollectionProbe[]
	 */
	public function getProbes() {
		return $this->probes;
	}

}

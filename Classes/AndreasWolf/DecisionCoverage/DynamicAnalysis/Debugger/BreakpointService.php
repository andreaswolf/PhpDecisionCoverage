<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger;

use AndreasWolf\DebuggerClient\Event\BreakpointEvent;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\LineBreakpoint;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\Breakpoint as DebuggerBreakpoint;
use AndreasWolf\DebuggerClient\Protocol\Command\BreakpointSet;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DebuggerEngineDataFetcher;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\InvocationSample;
use AndreasWolf\DecisionCoverage\StaticAnalysis\CounterProbe;
use AndreasWolf\DecisionCoverage\StaticAnalysis\DataCollectionProbe;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use React\Promise;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Component responsible for setting breakpoints and dealing with breakpoint hits.
 *
 * This will attach itself to the debugger client's event loop and react to session events.
 * One instance needs to be created per debugging session.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BreakpointService implements EventSubscriberInterface {

	/**
	 * The debugging session this service belongs to.
	 *
	 * @var DebugSession
	 */
	protected $session;

	/**
	 * @var ProbeConnector[]
	 */
	protected $probes = array();

	/**
	 * @var CoverageDataSet
	 */
	protected $coverageData;


	public function __construct(DebugSession $session, CoverageDataSet $dataSet) {
		$this->session = $session;
		$this->coverageData = $dataSet;
	}

	/**
	 * Sends commands to the debugger engine to set breakpoints for all given probes.
	 *
	 * The returned promise is resolved as soon as all breakpoints have been confirmed by the debugger engine.
	 *
	 * @param string $filePath
	 * @param Probe[] $probes
	 * @return Promise\Promise
	 */
	public function addBreakpointsForFile($filePath, $probes) {
		$promises = array();

		$probesByLine = $this->arrangeProbesByLine($probes);

		/** @var int $lineNumber  @var Probe[] $probes */
		foreach ($probesByLine as $lineNumber => $probes) {
			$debuggerBreakpoint = new LineBreakpoint($filePath, $lineNumber);

			$this->probes[] = new ProbeConnector($debuggerBreakpoint, $probes);

			$promises[] = $this->setBreakpoint($debuggerBreakpoint);
		}

		return Promise\all($promises);
	}

	/**
	 * Handles a hit to a breakpoint (i.e. initializes the data fetching).
	 *
	 * @param BreakpointEvent $event
	 * @return void
	 */
	public function breakpointHitHandler(BreakpointEvent $event) {
		$debuggerBreakpoint = $event->getBreakpoint();
		$probeConnector = $this->findProbesForBreakpoint($debuggerBreakpoint);

		$promises = [];
		foreach ($probeConnector->getProbes() as $probe) {
			$promises[] = $this->collectProbeData($probe);
		}

		if (count($promises) > 0) {
			$overallPromise = Promise\all($promises)->then(function() use ($event) {
				$event->getSession()->run();
			});
		} else {
			$event->getSession()->run();
		}
	}

	/**
	 * Collects data from the given probe during a program run.
	 *
	 * @param Probe $probe
	 * @return Promise\Promise
	 */
	protected function collectProbeData(Probe $probe) {
		$promise = NULL;
		if ($probe instanceof DataCollectionProbe) {
			if ($probe->hasWatchedExpressions()) {
				$fetcher = $this->getDataFetcher();
				$dataSet = new DataSample($probe);
				$fetchPromise = $fetcher->fetchValuesForExpressions($probe->getWatchedExpressions(), $dataSet);

				$fetchPromise->then(function() use ($dataSet) {
					$this->coverageData->addSample($dataSet);
				});

				$promise = $fetchPromise;
			} else {
				$promise = new Promise\FulfilledPromise();
			}
		} elseif ($probe instanceof CounterProbe) {
			$probe->countInvocation();
			$sample = new InvocationSample($probe);
			$this->coverageData->addSample($sample);

			$promise = new Promise\FulfilledPromise();
		} else {
			throw new \InvalidArgumentException('Unsupported probe type! ' . get_class($probe));
		}

		return $promise;
	}

	protected function getDataFetcher() {
		static $fetcher;

		if (!$fetcher) {
			$fetcher = new DebuggerEngineDataFetcher($this->session);
		}
		return $fetcher;
	}

	/**
	 * Looks at the breakpoints registered with the debugger and returns the probe connected
	 * with the matching breakpoint.
	 *
	 * @param DebuggerBreakpoint $breakpoint
	 * @return ProbeConnector The probe
	 * @throws \RuntimeException If no breakpoint was found.
	 */
	protected function findProbesForBreakpoint(DebuggerBreakpoint $breakpoint) {
		foreach ($this->probes as $probeConnector) {
			if ($probeConnector->getBreakpoint() === $breakpoint) {
				return $probeConnector;
			}
		}

		// no breakpoint was found
		throw new \RuntimeException('Could not find breakpoint.');
	}

	/**
	 * @param Probe[] $probes
	 * @return Probe[][] An array of probes, sorted by line. The first-level array keys are the line numbers
	 */
	protected function arrangeProbesByLine($probes) {
		$sortedProbes = [];
		foreach ($probes as $probe) {
			$line = $probe->getLine();
			if (!isset($sortedProbes[$line])) {
				$sortedProbes[$line] = [];
			}

			$sortedProbes[$line][] = $probe;
		}

		return $sortedProbes;
	}

	/**
	 * @param DebuggerBreakpoint $breakpoint
	 * @return Promise\Promise|Promise\PromiseInterface
	 */
	protected function setBreakpoint(DebuggerBreakpoint $breakpoint) {
		// TODO this turns the process of adding the breakpoints in DebuggingSession kind of upside-down
		// we should find a better way to get the promises
		$setCommand = new BreakpointSet($this->session, $breakpoint);

		$breakpointCollection = $this->session->getBreakpointCollection();
		$breakpointCollection->add($breakpoint);
		$promise = $setCommand->promise();
		$this->session->sendCommand($setCommand);

		return $promise;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents() {
		return array(
			'session.breakpoint.hit' => 'breakpointHitHandler',
		);
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger;

use AndreasWolf\DebuggerClient\Event\BreakpointEvent;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\LineBreakpoint;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\Breakpoint as DebuggerBreakpoint;
use AndreasWolf\DebuggerClient\Protocol\Command\BreakpointSet;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DebuggerEngineDataFetcher;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\PropertyValueFetcher;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\ValueFetch;
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
	 * @var [Probe,DebuggerBreakpoint]
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
	 * Sends commands to the debugger engine to set all given breakpoints.
	 *
	 * The returned promise is resolved as soon as all breakpoints have been confirmed by the debugger engine.
	 *
	 * @param string $filePath
	 * @param Probe[] $probes
	 * @return Promise\Promise
	 */
	public function addBreakpointsForFile($filePath, $probes) {
		$promises = array();
		$breakpointCollection = $this->session->getBreakpointCollection();

		foreach ($probes as $probe) {
			// TODO we should somehow attach the probe to the breakpoint to be able to have multiple probes for one
			// breakpoint -> it might be good to have e.g. invocation counting separated from actual data fetching
			$debuggerBreakpoint = new LineBreakpoint($filePath, $probe->getLine());
			$this->probes[] = array($probe, $debuggerBreakpoint);

			// TODO this turns the process of adding the breakpoints in DebuggingSession kind of upside-down
			// we should find a better way to get the promises
			$setCommand = new BreakpointSet($this->session, $debuggerBreakpoint);
			$breakpointCollection->add($debuggerBreakpoint);
			$promises[] = $setCommand->promise();
			$this->session->sendCommand($setCommand);
		}

		return Promise\all($promises);
	}

	public function breakpointHitHandler(BreakpointEvent $event) {
		$debuggerBreakpoint = $event->getBreakpoint();
		/** @var Probe $probe */
		$probe = NULL;
		foreach ($this->probes as $probeAndBreakpoint) {
			if ($probeAndBreakpoint[1] === $debuggerBreakpoint) {
				$probe = $probeAndBreakpoint[0];
			}
		}
		if ($probe === NULL) {
			throw new \RuntimeException('Could not find breakpoint.');
		}

		if ($probe->hasWatchedExpressions()) {
			// TODO if we ever need to handle multiple probes per breakpoint, this is the place to implement it…
			$fetcher = $this->getDataFetcher();
			$dataSet = new DataSample($probe);
			$fetchPromise = $fetcher->fetchValuesForExpressions($probe->getWatchedExpressions(), $dataSet);

			$fetchPromise->then(function() use ($dataSet, $event) {
				$this->coverageData->addSample($dataSet);

				// all data was fetched, proceed with session…
				$event->getSession()->run();
			});
		} else {
			$event->getSession()->run();
		}
	}

	protected function getDataFetcher() {
		static $fetcher;

		if (!$fetcher) {
			$fetcher = new DebuggerEngineDataFetcher($this->session);
		}
		return $fetcher;
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

<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger;

use AndreasWolf\DebuggerClient\Event\BreakpointEvent;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\LineBreakpoint;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\Breakpoint as DebuggerBreakpoint;
use AndreasWolf\DebuggerClient\Protocol\Command\BreakpointSet;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\BreakpointDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DebuggerEngineDataFetcher;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\PropertyValueFetcher;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\ValueFetch;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Breakpoint;
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
	 * @var [Breakpoint,DebuggerBreakpoint]
	 */
	protected $breakpoints = array();

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
	 * @param Breakpoint[] $breakpoints
	 * @return Promise\Promise
	 */
	public function addBreakpointsForFile($filePath, $breakpoints) {
		$promises = array();
		$breakpointCollection = $this->session->getBreakpointCollection();

		foreach ($breakpoints as $analysisBreakpoint) {
			$debuggerBreakpoint = new LineBreakpoint($filePath, $analysisBreakpoint->getLine());
			$this->breakpoints[] = array($analysisBreakpoint, $debuggerBreakpoint);

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
		/** @var Breakpoint $analysisBreakpoint */
		$analysisBreakpoint = NULL;
		foreach ($this->breakpoints as $registeredBreakpoint) {
			if ($registeredBreakpoint[1] === $debuggerBreakpoint) {
				$analysisBreakpoint = $registeredBreakpoint[0];
			}
		}
		if ($analysisBreakpoint === NULL) {
			throw new \RuntimeException('Could not find breakpoint.');
		}

		if ($analysisBreakpoint->hasWatchedExpressions()) {
			$fetcher = $this->getDataFetcher();
			$dataSet = new BreakpointDataSet($analysisBreakpoint);
			$fetchPromise = $fetcher->fetchValuesForExpressions($analysisBreakpoint->getWatchedExpressions(), $dataSet);

			$fetchPromise->then(function() use ($dataSet, $event) {
				$this->coverageData->addBreakpointDataSet($dataSet);

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

<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger;

use AndreasWolf\DebuggerClient\Event\BreakpointEvent;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\LineBreakpoint;
use AndreasWolf\DebuggerClient\Protocol\Command\BreakpointSet;
use AndreasWolf\DebuggerClient\Session\DebugSession;
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


	public function __construct(DebugSession $session) {
		$this->session = $session;
	}

	/**
	 * @param string $filePath
	 * @param Breakpoint[] $breakpoints
	 * @return Promise\Promise
	 */
	public function addBreakpointsForFile($filePath, $breakpoints) {
		$promises = array();
		$breakpointCollection = $this->session->getBreakpointCollection();

		foreach ($breakpoints as $breakpoint) {
			$debuggerBreakpoint = new LineBreakpoint($filePath, $breakpoint->getLine());

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
		// TODO fetch data
		$event->getSession()->run();
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

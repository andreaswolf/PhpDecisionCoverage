<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger;

use AndreasWolf\DebuggerClient\Core\Client;
use AndreasWolf\DebuggerClient\Event\BreakpointEvent;
use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\ProcessTestRunner;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\TestEventHandler;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\TestListenerOutputStream;
use AndreasWolf\DecisionCoverage\Event\TestEvent;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Listener for events in the debugger engine.
 *
 * Triggers test execution as soon as the debugger engine listening socket becomes ready.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ClientEventSubscriber implements EventSubscriberInterface {

	/**
	 * The debugger client instance this listener belongs to
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * @var string
	 */
	protected $staticAnalysisFile;

	/**
	 * @var ResultSet
	 */
	protected $staticAnalysisData;

	/**
	 * @var CoverageDataSet
	 */
	protected $dataSet;

	/**
	 * @var ProcessTestRunner
	 */
	protected $testRunner;


	public function __construct(Client $client, CoverageDataSet $coverageDataSet) {
		$this->client = $client;
		$this->dataSet = $coverageDataSet;

		$this->testRunner = new ProcessTestRunner($client);
	}

	/**
	 * @param string $staticAnalysisFile
	 */
	public function setStaticAnalysisFile($staticAnalysisFile) {
		$this->staticAnalysisFile = $staticAnalysisFile;
	}

	/**
	 * @param string $phpUnitArguments
	 */
	public function setPhpUnitArguments($phpUnitArguments) {
		$this->testRunner->setPhpUnitArguments(str_replace('\\', '', $phpUnitArguments));
	}

	/**
	 * @param Event $event
	 */
	public function listenerReadyHandler(Event $event) {
		echo "Client ready\n";
		$this->staticAnalysisData = $this->loadStaticAnalysisData();

		$this->testRunner->run($this->client);

		echo "Started running tests\n";
	}

	/**
	 * @param SessionEvent $event
	 * @return void
	 */
	public function sessionInitializedHandler(SessionEvent $event) {
		$session = $event->getSession();

		$breakpointService = new BreakpointService($session, $this->dataSet);
		$this->client->addSubscriber($breakpointService);

		$testEventHandler = new TestEventHandler($this->dataSet);
		$this->client->addSubscriber($testEventHandler);

		$promises = array();
		foreach ($this->staticAnalysisData->getFileResults() as $fileResult) {
			$promises[] = $breakpointService->addBreakpointsForFile($fileResult->getFilePath(), $fileResult->getBreakpoints());
		}

		\React\Promise\all($promises)->then(function() use ($session) {
			echo "All breakpoints set\n";
		}, function() {
			echo "Setting breakpoints failed\n";
		});

		$this->client->addListener('session.status.changed', function(SessionEvent $e) use ($session, $breakpointService) {
			if ($e->getSession() != $session) {
				return;
			}

			// session has ended, so remove breakpoint service
			if ($session->getStatus() == DebugSession::STATUS_STOPPED) {
				$this->client->removeSubscriber($breakpointService);
			}
		});
	}

	/**
	 * Loads the static analysis data gathered before.
	 *
	 * @return ResultSet
	 */
	protected function loadStaticAnalysisData() {
		$fileContents = file_get_contents($this->staticAnalysisFile);

		$analysisObject = unserialize($fileContents);

		return $analysisObject;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'listener.ready' => 'listenerReadyHandler',
			'session.initialized' => 'sessionInitializedHandler',
		);
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger;

use AndreasWolf\DebuggerClient\Core\Client;
use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\ProcessTestRunner;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\TestEventHandler;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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

	/** @var LoggerInterface */
	protected $logger;


	public function __construct(Client $client, CoverageDataSet $coverageDataSet, LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}
		$this->client = $client;
		$this->dataSet = $coverageDataSet;
		$this->logger = $logger;

		$this->testRunner = new ProcessTestRunner($this->logger);
	}

	/**
	 * @param ResultSet $results
	 */
	public function setStaticAnalysisResults(ResultSet $results) {
		$this->staticAnalysisData = $results;
	}

	/**
	 * @param array|string $phpUnitArguments
	 */
	public function setPhpUnitArguments($phpUnitArguments) {
		if (is_string($phpUnitArguments)) {
			$argumentsString = $phpUnitArguments;
		} else {
			$argumentsString = '';
			foreach ($phpUnitArguments as $name => $value) {
				$argumentsString .= $name . ' ' . $value;
			}
		}
		$this->testRunner->setPhpUnitArguments($argumentsString);
	}

	/**
	 * @param Event $event
	 */
	public function listenerReadyHandler(Event $event) {
		echo "Client ready\n";

		$this->testRunner->run($this->client);

		echo "Started running tests\n";
	}

	/**
	 * @param SessionEvent $event
	 * @return void
	 */
	public function sessionInitializedHandler(SessionEvent $event) {
		$session = $event->getSession();
		$session->disableAutorun();

		$breakpointService = new BreakpointService($session, $this->dataSet, $this->logger);
		$this->client->addSubscriber($breakpointService);

		$testEventHandler = new TestEventHandler($this->dataSet);
		$this->client->addSubscriber($testEventHandler);

		$promises = array();
		foreach ($this->staticAnalysisData->getFileResults() as $fileResult) {
			$promises[] = $breakpointService->addBreakpointsForFile($fileResult->getFilePath(), $fileResult->getProbes());
		}

		\React\Promise\all($promises)->then(function() use ($session) {
			echo "All breakpoints set\n";
			$session->run();
		}, function() {
			echo "Setting breakpoints failed\n";
		});

		$this->client->addListener('session.status.changed', function(SessionEvent $e) use ($session, $breakpointService) {
			if ($e->getSession() != $session) {
				return;
			}

			$this->logger->debug('Test process status: ' . $this->testRunner->getProcessStatus());

			// session has ended, so remove breakpoint service
			if ($session->getStatus() == DebugSession::STATUS_STOPPED) {
				$this->client->removeSubscriber($breakpointService);
			}
		});

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

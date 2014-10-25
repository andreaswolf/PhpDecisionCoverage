<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger;

use AndreasWolf\DebuggerClient\Core\Client;
use AndreasWolf\DebuggerClient\Event\BreakpointEvent;
use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\TestListenerOutputStream;
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
	protected $fifoFile;

	/**
	 * @var string
	 */
	protected $staticAnalysisFile;

	/**
	 * @var ResultSet
	 */
	protected $staticAnalysisData;

	/**
	 * @var string
	 */
	protected $phpUnitArguments;


	public function __construct(Client $client) {
		$this->client = $client;

		$this->fifoFile = sys_get_temp_dir() . '/fifo-' . uniqid();
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
		$this->phpUnitArguments = str_replace('\\', '', $phpUnitArguments);
	}

	/**
	 * @param Event $event
	 */
	public function listenerReadyHandler(Event $event) {
		echo "Client ready\n";
		$arguments = $this->getTestRunArguments();
		$this->prepareAndAttachFifoStream();
		$this->staticAnalysisData = $this->loadStaticAnalysisData();

		$command = '/usr/bin/env php ' . $arguments;

		$pipes = array();
		proc_open($command, array(
			//array('pipe', 'r'),
			//array('pipe', 'w'),
		), $pipes, NULL, array('XDEBUG_CONFIG' => 'IDEKEY=DecisionCoverage'));

		echo "Started running tests\n";
	}

	/**
	 * @param SessionEvent $event
	 * @return void
	 */
	public function sessionInitializedHandler(SessionEvent $event) {
		$session = $event->getSession();
		$coverageDataSet = new CoverageDataSet();
		$breakpointService = new BreakpointService($session, $coverageDataSet);
		$this->client->addSubscriber($breakpointService);

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
	 * Creates the FIFO (named pipe) used by the test runner to communicate the currently executed test
	 *
	 * @return void
	 */
	protected function prepareAndAttachFifoStream() {
		// Using "r+" will make the FIFO also writable from our side; this is necessary because currently there is nobody
		// else writing to it and thus opening it read-only would block until a writer gets attached. See
		// <http://de2.php.net/manual/en/function.posix-mkfifo.php#89642>.
		$fifo = posix_mkfifo($this->fifoFile, 0600);

		$fifoHandle = fopen($this->fifoFile, 'r+');
		$this->client->attachStream(new TestListenerOutputStream($fifoHandle));
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
	 * @return array
	 */
	protected function getTestRunArguments() {
		$arguments = implode(' ', array(
			// re-add the file we want to run
			realpath(__DIR__ . '/../../../../../Scripts/RunTest.php'),
			// add the fifo file name (this is not recognized by PHPUnit, but by our test script)
			'--fifo', $this->fifoFile,
			$this->phpUnitArguments
		));

		return $arguments;
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

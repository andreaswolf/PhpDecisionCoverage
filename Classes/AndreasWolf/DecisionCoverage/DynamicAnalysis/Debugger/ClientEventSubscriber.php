<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger;

use AndreasWolf\DebuggerClient\Core\Client;
use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\ProcessTestRunner;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\TestEventHandler;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\TestProgressReporter;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\OutputInterface;
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

	/** @var OutputInterface */
	protected $output;


	public function __construct(Client $client, CoverageDataSet $coverageDataSet, OutputInterface $output,
	                            LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}
		$this->client = $client;
		$this->dataSet = $coverageDataSet;
		$this->logger = $logger;
		$this->output = $output;

		$this->testRunner = new ProcessTestRunner($this->logger);

		if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
			$client->addSubscriber(new TestProgressReporter($this->output));
		}
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
		$this->output->writeln("<info>Client ready</info>");

		$this->testRunner->run($this->client);

		$this->output->writeln("<info>Started running tests</info>");
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
			$this->output->writeln("<info>All breakpoints set</info>");
			$session->run();
		}, function() {
			$this->output->writeln("<error>Setting (some) breakpoints failed</error>");
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
	 * @return array The event names to listen to
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'listener.ready' => 'listenerReadyHandler',
			'session.initialized' => 'sessionInitializedHandler',
		);
	}

}

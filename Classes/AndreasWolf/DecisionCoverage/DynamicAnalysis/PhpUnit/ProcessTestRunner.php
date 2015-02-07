<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit;
use AndreasWolf\DebuggerClient\Core\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;


/**
 * A test runner for PhpUnit that uses a script in a separate process to execute the tests.
 *
 * Communication is done via a FIFO queue, which is created in the process of letting the tests run. For com
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ProcessTestRunner {

	/**
	 * @var string
	 */
	protected $fifoFile;

	/**
	 * @var string
	 */
	protected $phpUnitArguments;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;


	public function __construct(LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}

		$this->logger = $logger;
	}

	public function run(Client $client) {
		$this->fifoFile = sys_get_temp_dir() . '/fifo-' . uniqid();
		$this->prepareAndAttachFifoStream($client);

		$arguments = $this->getTestRunArguments();
		array_unshift($arguments, '/usr/bin/env', 'php');

		$process = ProcessBuilder::create($arguments)
			->addEnvironmentVariables(array('XDEBUG_CONFIG' => 'IDEKEY=DecisionCoverage'))
			->getProcess();

		// we must use start instead of run() because we need to take back control when the process has started;
		// otherwise the process would hang indefinitely.
		$process->start();
	}

	/**
	 * @param string $phpUnitArguments
	 */
	public function setPhpUnitArguments($phpUnitArguments) {
		$this->phpUnitArguments = $phpUnitArguments;
	}

	/**
	 * @return array
	 */
	protected function getTestRunArguments() {
		$arguments = array(
			$this->getTestScriptPath(),
			// add the fifo file name (this is not recognized by PHPUnit, but by our test script)
			'--fifo', $this->fifoFile,
			$this->phpUnitArguments
		);

		return $arguments;
	}

	/**
	 * Creates the FIFO (named pipe) used by the test runner to communicate the currently executed test
	 *
	 * @param Client $client
	 * @return void
	 */
	protected function prepareAndAttachFifoStream(Client $client) {
		// Using "r+" will make the FIFO also writable from our side; this is necessary because currently there is nobody
		// else writing to it and thus opening it read-only would block until a writer gets attached. See
		// <http://de2.php.net/manual/en/function.posix-mkfifo.php#89642>.
		$fifo = posix_mkfifo($this->fifoFile, 0600);

		$fifoHandle = fopen($this->fifoFile, 'r+');
		$listenerOutputStream = new TestListenerOutputStream($fifoHandle, $client, $this->logger);

		$client->attachStream($listenerOutputStream);
	}

	/**
	 * @return string
	 */
	protected function getTestScriptPath() {
		return realpath(__DIR__ . '/../../../../../Scripts/RunTest.php');
	}

}
 
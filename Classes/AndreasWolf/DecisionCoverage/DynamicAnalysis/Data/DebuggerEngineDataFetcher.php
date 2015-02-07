<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use PhpParser\Node\Expr;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\Promise;


/**
 * Handles fetching data from the debugger engine (sends the respective commands, sets return value handling)
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebuggerEngineDataFetcher {

	/**
	 * @var ValueFetchStrategy[]
	 */
	protected $valueFetchers = array();

	/**
	 * @var LoggerInterface
	 */
	protected $logger;



	public function __construct(DebugSession $session, LoggerInterface $logger = NULL) {
		$this->valueFetchers = array(
			new PropertyValueFetcher($session),
			new EvalValueFetcher($session),
		);
		if (!$logger) {
			$logger = new NullLogger();
		}

		$this->logger = $logger;
	}

	/**
	 * Sends commands to fetch all given expressions.
	 *
	 * The data is added to the given data set as soon as it was returned by the debugger engine.
	 *
	 * @param Expr[] $expressions
	 * @param DataSample $dataSet The data set to store the fetched values in
	 * @return Promise\Promise
	 */
	public function fetchValuesForExpressions($expressions, DataSample $dataSet) {
		$promises = array();

		$this->logger->debug('Fetching values for ' . count($expressions) . ' expressions.');
		$i = 0;
		/** @var Expr $expression */
		foreach ($expressions as $expression) {
			++$i;
			$fetch = new ValueFetch($expression);
			$fetcher = $this->getFetcherForExpression($expression);

			$promise = $fetcher->fetch($fetch);
			$promise->then(function(ExpressionValue $value) use ($expression, $fetch, $dataSet, $i) {
				$this->logger->debug('Received value for expression ' . $i);
				// add the value to the data set when it was returned by the debugger engine
				$dataSet->addValue($expression, $value);
			});
			$promises[] = $promise;
		}

		return Promise\all($promises);
	}

	/**
	 * Returns the fetcher strategy for the given expression.
	 *
	 * @param Expr $expression
	 * @return ValueFetchStrategy
	 * @throw \RuntimeException If no fetcher can be found
	 */
	protected function getFetcherForExpression(Expr $expression) {
		foreach ($this->valueFetchers as $fetcher) {
			if ($fetcher->canFetch($expression)) {
				return $fetcher;
			}
		}
		throw new \RuntimeException('No fetcher found for expression of type ' . $expression->getType());
	}

}

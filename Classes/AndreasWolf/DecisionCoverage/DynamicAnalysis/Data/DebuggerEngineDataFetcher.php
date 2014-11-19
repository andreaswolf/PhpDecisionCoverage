<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use PhpParser\Node\Expr;
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



	public function __construct(DebugSession $session) {
		$this->valueFetchers = array(
			new EvalValueFetcher($session),
			new PropertyValueFetcher($session)
		);
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

		/** @var Expr $expression */
		foreach ($expressions as $expression) {
			$fetcher = $this->getFetcherForExpression($expression);
			$fetch = new ValueFetch($expression);

			if (!$fetcher) {
				throw new \RuntimeException('No fetcher found for ' . $fetch->getExpressionAsString());
			}

			$promise = $fetcher->fetch($fetch);
			$promise->then(function(ExpressionValue $value) use ($expression, $fetch, $dataSet) {
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
	 */
	protected function getFetcherForExpression(Expr $expression) {
		foreach ($this->valueFetchers as $fetcher) {
			if ($fetcher->canFetch($expression)) {
				return $fetcher;
			}
		}
	}

}

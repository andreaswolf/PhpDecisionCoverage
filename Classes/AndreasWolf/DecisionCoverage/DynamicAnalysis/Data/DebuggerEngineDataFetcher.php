<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DebuggerClient\Session\DebugSession;
use PhpParser\Node\Expr;
use React\Promise;


/**
 *
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
			new EvalValueFetcher(),
			new PropertyValueFetcher($session)
		);
	}

	/**
	 * Sends commands to fetch all given expressions.
	 *
	 * @param Expr[]
	 * @return Promise\Promise
	 */
	public function fetchValuesForExpressions($expressions) {
		$promises = array();

		/** @var Expr $expression */
		foreach ($expressions as $expression) {
			$fetcher = $this->getFetcherForExpression($expression);
			$fetch = new ValueFetch($expression);

			if (!$fetcher) {
				throw new \RuntimeException('No fetcher found for ' . $fetch->getExpressionAsString());
			}

			$promise = $fetcher->fetch($fetch);
			$promise->then(function($value) use ($fetch) {
				echo "Fetched value ", $value, " for ", $fetch->getExpressionAsString(), "\n";
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

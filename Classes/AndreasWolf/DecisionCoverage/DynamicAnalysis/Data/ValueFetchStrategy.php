<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use PhpParser\Node\Expr;
use React\Promise\Promise;


/**
 * Interface for strategies to fetch a value from the debugger engine.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface ValueFetchStrategy {

	/**
	 * Checks if this strategy can be used to fetch
	 *
	 * @param Expr $expression
	 * @return bool
	 */
	public function canFetch(Expr $expression);

	/**
	 * Fetches the given value
	 *
	 * @param ValueFetch $value
	 * @return Promise
	 */
	public function fetch(ValueFetch $value);

}

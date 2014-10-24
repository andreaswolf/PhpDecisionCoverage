<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use PhpParser\Node\Expr;
use React\Promise\Promise;


/**
 * Fetches a value using the DBGp "eval" method.
 *
 * The expressions that this fetcher can evaluate are (as of now) the exact opposite of those
 * that the PropertyValueFetcher can fetch.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class EvalValueFetcher implements ValueFetchStrategy {

	/**
	 * Checks if this strategy can be used to fetch the given expression.
	 *
	 * @param Expr $expression
	 * @return bool
	 */
	public function canFetch(Expr $expression) {
		$result = FALSE;
		if ($expression instanceof Expr\MethodCall) {
			$result = TRUE;
		} elseif ($expression instanceof Expr\PropertyFetch) {
			$result = self::expressionContainsMethodCall($expression);
		}
		return $result;
	}

	/**
	 * @param Expr $node
	 * @return bool
	 */
	public static function expressionContainsMethodCall(Expr $node) {
		$nodeIterator = new \RecursiveIteratorIterator(new SyntaxTreeIterator($node, TRUE), \RecursiveIteratorIterator::SELF_FIRST);
		foreach ($nodeIterator as $currentNode) {
			if ($currentNode instanceof Expr\MethodCall) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Fetches the given value
	 *
	 * @param ValueFetch $value
	 * @return Promise
	 */
	public function fetch(ValueFetch $value) {
		// TODO: Implement fetch() method.
		throw new \BadMethodCallException();
	}

}

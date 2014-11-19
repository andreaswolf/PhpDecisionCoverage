<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DebuggerClient\Protocol\Command\Evaluate;
use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use PhpParser\Node\Expr;
use React\Promise\FulfilledPromise;
use React\Promise\Promise;


/**
 * Fetches a value using the DBGp "eval" method.
 *
 * This fetcher can fetch any expression, also those that the PropertyValueFetcher can fetch. There is currently no
 * known disadvantage of this fetcher wrt to speed, so its safe to use it as a complete replacement for the
 * PropertyValueFetcher).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class EvalValueFetcher implements ValueFetchStrategy {

	/**
	 * @var DebugSession
	 */
	protected $debugSession;


	public function __construct(DebugSession $debugSession) {
		$this->debugSession = $debugSession;
	}

	/**
	 * Checks if this strategy can be used to fetch the given expression.
	 *
	 * @param Expr $expression
	 * @return bool
	 */
	public function canFetch(Expr $expression) {
		return ($expression instanceof Expr);
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
		$expression = $value->getExpressionAsString();
		$propertyGetCommand = new Evaluate($expression, $this->debugSession);

		$this->debugSession->sendCommand($propertyGetCommand);
		return $propertyGetCommand->promise();
	}

}

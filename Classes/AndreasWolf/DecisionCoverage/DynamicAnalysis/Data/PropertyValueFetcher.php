<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DebuggerClient\Protocol\Command\PropertyGet;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use PhpParser\Node\Expr;
use PhpParser\PrettyPrinterAbstract;
use React\Promise\Promise;


/**
 * Value fetcher that uses DBGP’s property_get command to fetch values
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class PropertyValueFetcher implements ValueFetchStrategy {

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
		$result = FALSE;

		if ($expression instanceof Expr\Variable) {
			$result = TRUE;
		} elseif ($expression instanceof Expr\PropertyFetch) {
			$result = !EvalValueFetcher::expressionContainsMethodCall($expression);
		}

		return $result;
	}

	/**
	 * Fetches the given value
	 *
	 * @param ValueFetch $value
	 * @return Promise
	 */
	public function fetch(ValueFetch $value) {
		$expression = $value->getExpressionAsString();
		$propertyGetCommand = new PropertyGet($this->debugSession, $expression);

		$this->debugSession->sendCommand($propertyGetCommand);
		return $propertyGetCommand->promise();
	}

}

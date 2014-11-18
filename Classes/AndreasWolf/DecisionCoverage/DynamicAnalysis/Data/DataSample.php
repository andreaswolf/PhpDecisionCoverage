<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\Test;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Breakpoint;
use PhpParser\Node\Expr;


/**
 * A set of values collected for one measurement.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DataSample {

	/**
	 * @var Breakpoint
	 */
	protected $breakpoint;

	/**
	 * @var Expr[]
	 */
	protected $expressions = array();

	/**
	 * The expression values fetched from the debugger engine, indexed by expression node id.
	 *
	 * @var ExpressionValue[]
	 */
	protected $values = array();

	/**
	 * @var Test
	 */
	protected $test;


	public function __construct(Breakpoint $breakpoint) {
		$this->breakpoint = $breakpoint;
	}

	/**
	 * @return Test
	 */
	public function getTest() {
		return $this->test;
	}

	/**
	 * @param Test $test
	 */
	public function setTest($test) {
		$this->test = $test;
	}

	/**
	 * @return Breakpoint
	 */
	public function getBreakpoint() {
		return $this->breakpoint;
	}

	/**
	 * Adds the value for the given expression.
	 *
	 * Uses the coverage node id set by the static analysis to store the value.
	 *
	 * @param Expr $expression
	 * @param ExpressionValue $value
	 */
	public function addValue(Expr $expression, ExpressionValue $value) {
		$nodeId = $expression->getAttribute('coverage__nodeId');
		if (!$nodeId) {
			throw new \RuntimeException('Coverage node ID not found.');
		}
		if (array_key_exists($nodeId, $this->expressions)) {
			throw new \InvalidArgumentException('Value for node id ' . $nodeId . ' already added.', 1415299394);
		}

		$this->expressions[$nodeId] = $expression;
		$this->values[$nodeId] = $value;
	}

	/**
	 * @param Expr|int $nodeIdOrExpression The expression or its syntax tree node ID
	 * @return ExpressionValue
	 */
	public function getValue($nodeIdOrExpression) {
		if ($nodeIdOrExpression instanceof Expr) {
			$nodeIdOrExpression = $nodeIdOrExpression->getAttribute('coverage__nodeId');
		}

		return $this->values[$nodeIdOrExpression];
	}

}

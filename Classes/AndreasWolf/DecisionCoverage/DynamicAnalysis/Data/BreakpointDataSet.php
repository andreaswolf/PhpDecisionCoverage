<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;
use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Breakpoint;
use PhpParser\Node\Expr;


/**
 * A set of values collected for one breakpoint hit.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BreakpointDataSet {

	/**
	 * @var Breakpoint
	 */
	protected $breakpoint;

	/**
	 * @var Expr[]
	 */
	protected $expressions = array();

	/**
	 * @var ExpressionValue[]
	 */
	protected $values = array();


	public function __construct(Breakpoint $breakpoint) {
		$this->breakpoint = $breakpoint;
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

		if (!array_key_exists($nodeId, $this->expressions)) {
			$this->expressions[$nodeId] = $expression;
		}
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

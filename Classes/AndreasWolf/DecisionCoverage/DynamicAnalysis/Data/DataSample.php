<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\Test;
use AndreasWolf\DecisionCoverage\StaticAnalysis\DataCollectionProbe;
use PhpParser\Node\Expr;


/**
 * A set of values collected for one measurement, i.e. one breakpoint hit.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DataSample implements Sample {

	/**
	 * @var DataCollectionProbe
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


	public function __construct(DataCollectionProbe $breakpoint) {
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
	public function setTest(Test $test) {
		$this->test = $test;
	}

	/**
	 * @return DataCollectionProbe
	 */
	public function getProbe() {
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
	public function getValueFor($nodeIdOrExpression) {
		$this->ensureNodeId($nodeIdOrExpression);

		if (!isset($this->values[$nodeIdOrExpression])) {
			throw new \InvalidArgumentException('Could not find value for expression ' . $nodeIdOrExpression);
		}

		return $this->values[$nodeIdOrExpression];
	}

	/**
	 * Returns TRUE if a value for the given node exists in this sample.
	 *
	 * @param Expr|int $nodeIdOrExpression
	 * @return bool
	 */
	public function hasValueFor($nodeIdOrExpression) {
		$this->ensureNodeId($nodeIdOrExpression);

		return array_key_exists($nodeIdOrExpression, $this->values);
	}

	/**
	 * @param Expr|int &$nodeIdOrExpression
	 * @return void
	 */
	protected function ensureNodeId(&$nodeIdOrExpression) {
		if ($nodeIdOrExpression instanceof Expr) {
			$nodeIdOrExpression = $nodeIdOrExpression->getAttribute('coverage__nodeId');
		}
	}

}

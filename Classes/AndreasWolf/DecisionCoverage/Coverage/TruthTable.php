<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition;
use AndreasWolf\DecisionCoverage\Source\ComparisonExtractor;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\If_;


/**
 * A decision table for a number of conditions.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TruthTable {

	/**
	 * @var int
	 */
	protected $dimension;

	/**
	 * All the conditions that are covered by this decision table, in the order in which they are covered.
	 *
	 * The order is important e.g. if a set of input values is covered by both the first and the second condition, the
	 * first one will always win because they are evaluated in the exact order they are defined.
	 *
	 * @var BooleanCondition[]
	 */
	protected $conditionBlocks;

	/**
	 * The expressions used in the conditions.
	 *
	 * @var array
	 */
	protected $comparisonExpressions;

	/**
	 * @param BooleanCondition[] $conditions
	 */
	public function __construct(array $conditions) {
		$this->conditionBlocks = $conditions;

		$extractor = new ComparisonExtractor();
		$this->comparisonExpressions = $extractor->extractComparisons($this->conditionBlocks);
	}

	/**
	 * @return \AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition[]
	 */
	public function getConditionBlocks() {
		return $this->conditionBlocks;
	}

	/**
	 * Returns an array of the expressions used in the conditions, without the boolean/logical operators
	 * (e.g. &&, ||, AND, XOR).
	 *
	 * These are the expressions that are covered by this table.
	 *
	 * @return array
	 */
	public function getExpressions() {
		return $this->comparisonExpressions;
	}

	/**
	 * Returns all comparison expressions used in this decision table grouped by the variable that is compared.
	 *
	 * @return array
	 */
	public function getExpressionsByVariableName() {
		$groupedExpressions = array();
		foreach ($this->comparisonExpressions as $comparison) {
			foreach (array($comparison->left, $comparison->right) as $expression) {
				$variable = $this->getVariableNameFromExpression($expression);
				if ($variable === NULL) {
					continue;
				}

				if (!isset($groupedExpressions[$variable])) {
					$groupedExpressions[$variable] = array();
				}

				$groupedExpressions[$variable][] = $comparison;
			}
		}

		return $groupedExpressions;
	}

	/**
	 * Returns the variable names covered by the comparisons of this decision table.
	 *
	 * @return string[]
	 */
	public function getCoveredVariableNames() {
		return $this->extractVariableNamesFromComparisons($this->comparisonExpressions);
	}

	/**
	 * @param Expr\BinaryOp[] $comparisons
	 * @return string[]
	 */
	protected function extractVariableNamesFromComparisons($comparisons) {
		$variableNames = array();
		foreach ($comparisons as $comparison) {
			$variableName = NULL;
			foreach (array($comparison->left, $comparison->right) as $expression) {
				$variableName = $this->getVariableNameFromExpression($expression);

				if ($variableName !== NULL) {
					$variableNames[] = $variableName;
					// do not leave the inner foreach here because we might have a comparison of two variables, in
					// which case both variables have to be added
				}
			}
		}

		return array_unique($variableNames);
	}

	/**
	 * Checks if an expression refers to a variable and returns the name if so.
	 *
	 * @param Expr $expression The expression to extract the variable name
	 * @return string|NULL The variable name or NULL if the given expression cannot be resolved to a variable name
	 */
	protected function getVariableNameFromExpression(Expr $expression) {
		if ($expression instanceof Scalar) {
			return NULL;
		}

		if ($expression instanceof Expr\Variable) {
			return $expression->name;
		}
		return NULL;
	}

}

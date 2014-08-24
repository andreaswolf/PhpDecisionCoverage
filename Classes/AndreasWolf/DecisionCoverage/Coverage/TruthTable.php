<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DecisionCoverage\Source\ComparisonExtractor;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\If_;


/**
 * A decision table for an if statement and its else if and else statement(s).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class TruthTable {

	/**
	 * @var int
	 */
	protected $dimension;

	/**
	 * The expressions used in the conditions.
	 *
	 * @var array
	 */
	protected $comparisonExpressions;

	/**
	 * @param If_ $conditionNode
	 */
	public function __construct(If_ $conditionNode) {
		$this->parseConditionsFromIfNode($conditionNode);
	}

	/**
	 * Parses all variables from an if node
	 *
	 * @param If_ $ifNode
	 */
	protected function parseConditionsFromIfNode(If_ $ifNode) {
		$condition = $ifNode->cond;

		$comparisonExtractor = new ComparisonExtractor();
		$ifComparisons = $comparisonExtractor->extractFromIf($ifNode);
		$this->comparisonExpressions = $ifComparisons;
	}

	/**
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
 
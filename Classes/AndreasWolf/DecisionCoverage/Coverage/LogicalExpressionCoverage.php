<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use PhpParser\Node\Expr;


abstract class LogicalExpressionCoverage implements CompoundConditionCoverage {

	/**
	 * @var Expr
	 */
	protected $leftExpression;

	/**
	 * @var int
	 */
	protected $leftExpressionId;

	/**
	 * @var Expr
	 */
	protected $rightExpression;

	/**
	 * @var int
	 */
	protected $rightExpressionId;

	/**
	 * @var array
	 */
	protected $dataSets = array();


	/**
	 * @param Expr $expression The covered expression
	 */
	public function __construct(Expr $expression) {
		if (!$expression instanceof Expr\BinaryOp) {
			//throw new
		}
		$this->leftExpression = $expression->left;
		$this->leftExpressionId = $expression->left->getAttribute('coverage__nodeId');
		$this->rightExpression = $expression->right;
		$this->rightExpressionId = $expression->right->getAttribute('coverage__nodeId');
	}

	/**
	 * @param DataSample $dataSet
	 */
	public function recordCoveredInput(DataSample $dataSet) {
		$valueCombination = array(
			$dataSet->getValueFor($this->leftExpressionId), $dataSet->getValueFor($this->rightExpressionId)
		);

		$this->dataSets[] = $valueCombination;
	}

	/**
	 * @param boolean $leftValue
	 * @param boolean $rightValue
	 * @return boolean
	 */
	protected function isValueCombinationCovered($leftValue, $rightValue) {
		/** @var ExpressionValue[] $set */
		foreach ($this->dataSets as $set) {
			if ($set[0]->equalTo($leftValue) && $set[1]->equalTo($rightValue)) {
				return TRUE;
			}
		}
		return FALSE;
	}

}

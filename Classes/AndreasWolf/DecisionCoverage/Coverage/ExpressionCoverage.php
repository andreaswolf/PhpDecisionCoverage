<?php
namespace AndreasWolf\DecisionCoverage\Coverage;


use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use PhpParser\Node\Expr;


abstract class ExpressionCoverage implements Coverage {

	/**
	 * @var Expr
	 */
	protected $expression;

	/**
	 * @var array
	 */
	protected $coveredValues = array();


	/**
	 * @param Expr $expression The covered expression
	 */
	public function __construct(Expr $expression) {
		$this->expression = $expression;
	}

	/**
	 * @param ExpressionValue $value
	 * @return void
	 */
	public function recordCoveredValue(ExpressionValue $value) {
		$this->coveredValues[] = $value->getRawValue();
	}

	/**
	 * @param string $sampleId The ID of the data sample
	 * @return boolean
	 * @throws \InvalidArgumentException If the sample has no value for this expression
	 */
	public function getValueForSample($sampleId) {
		//
	}

}
 
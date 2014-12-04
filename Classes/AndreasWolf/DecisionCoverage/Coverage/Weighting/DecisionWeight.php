<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Weighting;


use PhpParser\Node\Expr;


/**
 * The weighting function for a decision expression.
 *
 * This connects the weights of its two parts by the given weight factors.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
abstract class DecisionWeight implements ExpressionWeight {

	/**
	 * @var ExpressionWeight
	 */
	protected $leftWeight;

	/**
	 * @var ExpressionWeight
	 */
	protected $rightWeight;

	/**
	 * @param ExpressionWeight $leftWeight
	 * @param ExpressionWeight $rightWeight
	 */
	public function __construct(ExpressionWeight $leftWeight, ExpressionWeight $rightWeight) {
		$this->leftWeight = $leftWeight;
		$this->rightWeight = $rightWeight;
	}

}
 
<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Weighting;

use PhpParser\Node\Expr;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ExpressionWeightBuilder {

	/**
	 * @var ExpressionWeightFactory
	 */
	protected $weightFactory;

	public function __construct(ExpressionWeightFactory $weightFactory = NULL) {
		if (!$weightFactory) {
			$weightFactory = new ExpressionWeightFactory();
		}

		$this->weightFactory = $weightFactory;
	}


	/**
	 * Builds the weight for the given expression (and, if necessary, its subexpressions).
	 *
	 * @param Expr $expression
	 * @return void
	 */
	public function buildForExpression(Expr $expression) {
		$weight = $this->weightFactory->createForCondition($expression);

		$expression->setAttribute('coverage__weight', $weight);
	}

}

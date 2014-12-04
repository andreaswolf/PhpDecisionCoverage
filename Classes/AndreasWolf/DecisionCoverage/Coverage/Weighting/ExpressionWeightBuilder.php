<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Weighting;

use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use PhpParser\Node\Expr;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ExpressionWeightBuilder {

	/**
	 * @var array
	 */
	protected $expressionStack;

	/**
	 * @var ExpressionWeightFactory
	 */
	protected $weightFactory;

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;


	public function __construct(ExpressionWeightFactory $weightFactory = NULL,
	                            ExpressionService $expressionService = NULL) {
		if (!$weightFactory) {
			$weightFactory = new ExpressionWeightFactory();
		}
		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}

		$this->weightFactory = $weightFactory;
		$this->expressionService = $expressionService;
	}

	/**
	 * Builds the weight for the given expression (and, if necessary, its subexpressions).
	 *
	 * @param Expr $expression
	 * @return void
	 */
	public function buildForExpression(Expr $expression) {
		$this->expressionStack = array();
		$this->buildExpressionStack($expression);

		while (count($this->expressionStack) > 0) {
			// examine the last element of the stack…
			$stackEntry = array_pop($this->expressionStack);

			// … to check if we need to resolve the partial weights …
			if (is_array($stackEntry)) {
				/** @var Expr[] $expressionSubparts */
				$expressionSubparts = array_slice($stackEntry, 1);
				/** @var Expr\BinaryOp $expression */
				$expression = $stackEntry[0];

				$leftSubpartWeight = $expressionSubparts[0]->getAttribute('coverage__weight');
				$rightSubpartWeight = $expressionSubparts[1]->getAttribute('coverage__weight');
				$decisionWeight = $this->weightFactory->createForDecision($expression, $leftSubpartWeight, $rightSubpartWeight);
				$expression->setAttribute('coverage__weight', $decisionWeight);
			} else {
				// … or can directly build the weight for it
				$this->buildAndAttachConditionWeight($stackEntry);
			}
		}
	}

	/**
	 * Builds a stack of expressions to examine for their weight.
	 *
	 * The resulting array can be processed backwards. All necessary weights for an encountered element will
	 * have been calculated before then.
	 *
	 * @param Expr $expression
	 * @return array
	 */
	protected function buildExpressionStack(Expr $expression) {
		$expressionsToExamine = array($expression);

		while (count($expressionsToExamine) > 0) {
			$currentExpression = array_shift($expressionsToExamine);

			if ($this->expressionService->isDecisionExpression($currentExpression)) {
				$this->expressionStack[] = array($currentExpression, $currentExpression->left, $currentExpression->right);

				// put the two new subexpressions on our list
				$expressionsToExamine[] = $currentExpression->left;
				$expressionsToExamine[] = $currentExpression->right;
			} else {
				$this->expressionStack[] = $currentExpression;
			}
		}
	}

	/**
	 * @param Expr $expression
	 * @return ConditionWeight
	 */
	protected function buildAndAttachConditionWeight(Expr $expression) {
		$weight = $this->weightFactory->createForCondition($expression);

		$expression->setAttribute('coverage__weight', $weight);

		return $weight;
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\Coverage\Input\DecisionInput;


/**
 * Interface for helper classes that evaluate a given binary operation.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface DecisionEvaluator {

	/**
	 * Evaluates the decision for the given set of input values
	 *
	 * @param DecisionInput $input
	 * @return EvaluationResult
	 */
	public function evaluate(DecisionInput $input);

}

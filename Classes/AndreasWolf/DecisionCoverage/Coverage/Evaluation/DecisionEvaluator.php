<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;


/**
 * Interface for helper classes that keep track of decision in- and outputs.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface DecisionEvaluator {

	public function recordInputValue(ExpressionValue $value);

	public function isShorted();

	public function finishEvaluation();

	public function getOutput();

}

<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DecisionCoverage\Coverage\Builder\DataSampleInputBuilder;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use PhpParser\Node\Expr;


/**
 * Directs the evaluation of a decision for one or more data samples.
 *
 * This class is the central part of the decision evaluation, using the other classes in this namespace as helpers.
 *
 * @author Andreas Wolf <aw@foundata.net>
 * @deprecated This class should be removed in favor of DataSampleInputBuilder
 */
class DecisionEvaluationDirector {

	/**
	 * @var Expr
	 */
	protected $decision;

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;


	/**
	 * @param Expr $decision
	 * @param ExpressionService $expressionService
	 */
	public function __construct(Expr $decision, $expressionService = NULL) {
		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}

		$this->decision = $decision;
		$this->expressionService = $expressionService;
	}

	/**
	 * @param DataSample $sample
	 * @return DecisionSample
	 */
	public function evaluate(DataSample $sample) {
		$builder = new DataSampleInputBuilder();
		$result = $builder->buildInputForSample($this->decision, $sample);

		$decisionSample = new DecisionSample($result, $builder->getShortedVariables(),
			$result->getValueForCondition($this->decision->getAttribute('coverage__nodeId')), $sample);

		return $decisionSample;
	}

}

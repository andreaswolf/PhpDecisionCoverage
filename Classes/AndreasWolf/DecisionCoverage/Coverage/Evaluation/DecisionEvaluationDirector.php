<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Evaluation;

use AndreasWolf\DecisionCoverage\Coverage\StackingIterator;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use AndreasWolf\DecisionCoverage\Source\DecisionIterator;
use PhpParser\Node\Expr;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * Directs the evaluation of a decision for one or more data samples.
 *
 * This class is the central part of the decision evaluation, using the other classes in this namespace as helpers.
 *
 * @author Andreas Wolf <aw@foundata.net>
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
	 * @return DecisionOutput
	 */
	public function evaluate(DataSample $sample) {
		$evaluatorEventDispatcher = new EventDispatcher();
		$iterator = new StackingIterator(
			new DecisionIterator($this->decision, TRUE), \RecursiveIteratorIterator::SELF_FIRST, 0,
			$evaluatorEventDispatcher
		);
		$visitor = new SyntaxTreeNodeVisitor($iterator, $sample, $this->expressionService);
		$evaluatorEventDispatcher->addSubscriber($visitor);

		foreach ($iterator as $item) {
			// this is a no-op, every relevant feature is handled in the visitor.
		}
		$decisionOutput = new DecisionOutput($visitor->getInputValues(), $visitor->getRootEvaluator()->getOutput(), $sample);

		return $decisionOutput;
	}

}

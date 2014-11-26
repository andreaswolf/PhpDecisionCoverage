<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\MCDC\DecisionCoverage;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\Service\ExpressionService;
use PhpParser\Node\Expr;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class CoverageFactory {

	/**
	 * @var ExpressionService
	 */
	protected $expressionService;

	/**
	 * @var CoverageBuilderFactory
	 */
	protected $coverageBuilderFactory;


	public function __construct(ExpressionService $expressionService = NULL) {
		if (!$expressionService) {
			$expressionService = new ExpressionService();
		}

		$this->expressionService = $expressionService;
	}

	public function canCreateCoverage(Expr $node) {
		return ($node instanceof Expr\BinaryOp);
	}

	/**
	 * @param Expr $node
	 * @return DecisionCoverage|SingleConditionCoverage
	 */
	public function createCoverageForNode(Expr $node) {
		if ($this->expressionService->isDecisionExpression($node)) {
			return $this->coverageBuilderFactory->createBuilderForDecision($node);
		} else {
			return new SingleConditionCoverage($node);
		}
	}

}
 
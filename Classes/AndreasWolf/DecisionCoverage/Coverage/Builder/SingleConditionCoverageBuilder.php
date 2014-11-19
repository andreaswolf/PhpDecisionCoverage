<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;

use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use PhpParser\Node;


/**
 *
 * TODO make this a generic coverage builder (i.e. rename it and use CoverageFactory)
 * @author Andreas Wolf <aw@foundata.net>
 */
class SingleConditionCoverageBuilder implements DataSampleVisitor {

	/**
	 * @var Node\Expr
	 */
	protected $expression;

	/**
	 * @var SingleConditionCoverage
	 */
	protected $coverage;


	/**
	 * @param Node\Expr $expression The node this builder should generate the coverage for
	 */
	public function __construct(Node\Expr $expression) {
		$this->expression = $expression;
		// TODO use factory instead
		$this->coverage = new SingleConditionCoverage($expression);
	}

	/**
	 * Called before the last data sample is traversed
	 *
	 * @return void
	 */
	public function startTraversal() {
		// TODO: Implement startTraversal() method.
	}

	/**
	 * Called when the last data sample has been traversed.
	 *
	 * @return Coverage
	 */
	public function endTraversal() {
		return $this->coverage;
	}

	/**
	 * Called for each data sample.
	 *
	 * @param DataSample $sample
	 * @return void
	 */
	public function handleSample(DataSample $sample) {
		// TODO this is generic code -> move it to a generic handler
		if (!$sample->hasValueFor($this->expression)) {
			return;
		}

		$value = $sample->getValueFor($this->expression);
		$this->coverage->recordCoveredValue($value);
	}

}

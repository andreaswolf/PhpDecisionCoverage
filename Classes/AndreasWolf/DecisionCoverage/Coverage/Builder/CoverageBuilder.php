<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Builder;
use AndreasWolf\DecisionCoverage\Coverage\Coverage;
use AndreasWolf\DecisionCoverage\Coverage\SingleConditionCoverage;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\CoverageDataSet;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use AndreasWolf\DecisionCoverage\StaticAnalysis\ResultSet;


/**
 * @author Andreas Wolf <aw@foundata.net>
 *
 * TODO rename to CoverageBuilder
 */
class CoverageBuilder {

	/**
	 * @var Probe[]
	 */
	protected $knownProbes = array();

	/**
	 * @var DataSampleVisitor[]
	 */
	protected $visitors = array();

	/**
	 * @param CoverageDataSet $dataSet
	 * @return Coverage[]
	 */
	public function buildFromDataSet(CoverageDataSet $dataSet) {
		// TODO this must use a more concise method -> align analysis with the file hierarchy and use the complete ASTs
		// to create the factory structure


		// the samples may be for any probe in any order, so we can never be sure that we have already handled a probe
		foreach ($dataSet->getSamples() as $sample) {
			$this->ensureFactoriesForProbeExpressionsArePresent($sample->getProbe());

			$this->visitDataSample($sample);
		}

		$results = array();
		foreach ($this->visitors as $visitor) {
			$results[] = $visitor->endTraversal();
		}
		return $results;
	}

	protected function visitDataSample(DataSample $sample) {
		foreach ($this->visitors as $visitor) {
			$visitor->handleSample($sample);
		}
	}

	protected function ensureFactoriesForProbeExpressionsArePresent(Probe $probe) {
		if (in_array($probe, $this->knownProbes)) {
			// we have already created visitors for this probe’s expressions
			return;
		}
		foreach ($probe->getWatchedExpressions() as $expression) {
			if (in_array($expression->getType(), array('Expr_BinaryOp_Identical'))) { // TODO extend with more types/move check to static method
				$visitor = new SingleConditionCoverageBuilder($expression);
				$visitor->startTraversal();
				$this->visitors[] = $visitor;
			}
		}
	}

}
 
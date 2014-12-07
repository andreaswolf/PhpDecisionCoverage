<?php
namespace AndreasWolf\DecisionCoverage\Tests\Helpers;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\DataSample;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use AndreasWolf\DecisionCoverage\Tests\Helpers\PhpParser\ExpressionMockBuilder;
use PhpParser\Node\Expr;


class DataSampleBuilder {

	/**
	 * @var ExpressionMockBuilder[]
	 */
	protected $expressionBuilders = array();

	/**
	 * @var DataSample
	 */
	protected $dataSample;


	public function __construct() {
		$this->dataSample = new DataSample(new Probe(1));
	}

	public function addMockedExpression($id, $class = '') {
		if (!$class) {
			$class = 'PhpParser\Node\Expr';
		}

		$this->expressionBuilders[$id] = new ExpressionMockBuilder($class);
		$this->expressionBuilders[$id]->setId($id);

		return $this->expressionBuilders[$id];
	}

	public function getExpressionMock($id) {
		return $this->expressionBuilders[$id]->getMock();
	}

	public function addSampleValue($expressionId, $value) {
		if (!gettype($value) == 'boolean') {
			throw new \InvalidArgumentException('Sample value must be a boolean.');
		}
		$expression = $this->expressionBuilders[$expressionId]->getMock();

		$this->dataSample->addValue($expression, new ExpressionValue(ExpressionValue::TYPE_BOOLEAN, $value));
	}

	public function getSample() {
		return $this->dataSample;
	}

}

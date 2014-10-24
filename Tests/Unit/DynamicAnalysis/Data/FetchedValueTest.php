<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\DynamicAnalysis\Data;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\ValueFetch;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;


class FetchedValueTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function expressionCanBePrettyPrinted() {
		$subject = new ValueFetch(new Expr\Variable('foo'));

		$this->assertEquals('$foo', $subject->getExpressionAsString());
	}

}

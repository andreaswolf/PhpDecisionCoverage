<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\BreakpointFactory;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\If_;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BreakpointFactoryTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function breakpointIsCreatedForIfStatement() {
		$ifNode = new If_(new Expr\Variable('foo'));
		$mockedAnalysis = $this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\FileAnalysis')
			->disableOriginalConstructor()->getMock();
		$mockedAnalysis->expects($this->once())->method('addBreakpoint');

		$subject = new BreakpointFactory($mockedAnalysis);

		$subject->startInstrumentation(array($ifNode));
		$subject->handleNode($ifNode);
	}

}

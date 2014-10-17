<?php
namespace AndreasWolf\PhpDecisionCoverage\Tests\Unit\StaticAnalysis\SyntaxTree;

use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Instrumenter;


/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class InstrumenterTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function manipulatorIsNotifiedAboutStartAndEndOfTraversal() {
		$nodes = array($this->getMockForAbstractClass('PhpParser\NodeAbstract'));
		$subject = new Instrumenter();
		$manipulator = $this->getMock('AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\NodeVisitor');

		$manipulator->expects($this->once())->method('startInstrumentation');
		$manipulator->expects($this->once())->method('endInstrumentation');
		$subject->addVisitor($manipulator);

		$subject->instrument($nodes);
	}

	/**
	 * @test
	 */
	public function manipulatorGetsCalledForRootNode() {
		$nodes = array($this->getMockForAbstractClass('PhpParser\NodeAbstract'));
		$subject = new Instrumenter();
		$manipulator = $this->getMock('AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\NodeVisitor');

		$manipulator->expects($this->once())->method('handleNode')->with($this->identicalTo($nodes[0]));
		$subject->addVisitor($manipulator);

		$subject->instrument($nodes);
	}

}

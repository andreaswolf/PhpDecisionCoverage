<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\StaticAnalysis\Probe;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\ProbeFactory;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ProbeFactoryTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function breakpointIsCreatedForIfStatement() {
		$ifNode = new Stmt\If_(new Expr\Variable('foo'));
		$mockedAnalysis = $this->mockFileAnalysis();
		$mockedAnalysis->expects($this->once())->method('addBreakpoint');

		$subject = new ProbeFactory($mockedAnalysis);

		$subject->startInstrumentation(array($ifNode));
		$subject->handleNode($ifNode);
	}

	/**
	 * @test
	 */
	public function breakpointIsCreatedForElseIfStatement() {
		$ifNode = new Stmt\ElseIf_(new Expr\Variable('foo'));
		$mockedAnalysis = $this->mockFileAnalysis();
		$mockedAnalysis->expects($this->once())->method('addBreakpoint');

		$subject = new ProbeFactory($mockedAnalysis);

		$subject->startInstrumentation(array($ifNode));
		$subject->handleNode($ifNode);
	}

	/**
	 * @test
	 */
	public function variableExpressionFromIfConditionIsAddedAsWatcher() {
		$variable = new Expr\Variable('foo');
		$ifNode = new Stmt\If_($variable);
		$mockedAnalysis = $this->mockFileAnalysis();

		$mockedBreakpoint = $this->mockProbe();
		$mockedBreakpoint->expects($this->once())->method('addWatchedExpression')->with($this->identicalTo($variable));
		$subject = $this->mockProbeFactory($mockedAnalysis, array($mockedBreakpoint));

		/** @var $subject ProbeFactory */
		$subject->startInstrumentation(array($ifNode));
		$subject->handleNode($ifNode);
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockFileAnalysis() {
		$mockedAnalysis = $this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\FileResult')
			->disableOriginalConstructor()->getMock();

		return $mockedAnalysis;
	}

	/**
	 * Creates an instance of the factory that does not use self-created breakpoints, but instead
	 * uses the ones given as parameter to this method.
	 *
	 * @param $mockedAnalysis
	 * @param Probe[] $breakpoints
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockProbeFactory($mockedAnalysis, $breakpoints) {
		$subject = $this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\ProbeFactory')
			->setConstructorArgs(array($mockedAnalysis))
			->setMethods(array('createBreakpoint'))
			->getMock();

		$i = 0;
		foreach ($breakpoints as $breakpoint) {
			$subject->expects($this->at($i))
				->method('createBreakpoint')->will($this->returnValue($breakpoint));
			++$i;
		}

		return $subject;
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockProbe() {
		$mockedBreakpoint = $this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\Probe')
			->disableOriginalConstructor()->getMock();

		return $mockedBreakpoint;
	}

}

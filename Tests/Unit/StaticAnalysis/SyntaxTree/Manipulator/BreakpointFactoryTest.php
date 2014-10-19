<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\StaticAnalysis\SyntaxTree\Manipulator;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\Data\Breakpoint;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\BreakpointFactory;
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
class BreakpointFactoryTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function breakpointIsCreatedForIfStatement() {
		$ifNode = new Stmt\If_(new Expr\Variable('foo'));
		$mockedAnalysis = $this->mockFileAnalysis();
		$mockedAnalysis->expects($this->once())->method('addBreakpoint');

		$subject = new BreakpointFactory($mockedAnalysis);

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

		$subject = new BreakpointFactory($mockedAnalysis);

		$subject->startInstrumentation(array($ifNode));
		$subject->handleNode($ifNode);
	}

	/**
	 * @test
	 */
	public function variableExpressionFromConditionIsAddedAsWatcher() {
		$variable = new Expr\Variable('foo');
		$ifNode = new Stmt\If_($variable);
		$mockedAnalysis = $this->mockFileAnalysis();

		$mockedBreakpoint = $this->mockBreakpoint();
		$mockedBreakpoint->expects($this->once())->method('addWatchedExpression')->with($this->identicalTo($variable));
		$subject = $this->mockBreakpointFactory($mockedAnalysis, array($mockedBreakpoint));

		/** @var $subject BreakpointFactory */
		$subject->startInstrumentation(array($ifNode));
		$subject->handleNode($ifNode);
	}

	public function watchableExpressionsProvider() {
		return array(
			'local variable access' => array(
				new Expr\Variable('foo')
			),
			'class property access' => array(
				new Expr\PropertyFetch(
					new Expr\Variable('this'), 'foo'
				)
			),
			'static class property' => array(
				new Expr\StaticPropertyFetch(
					new Name('Foo'), 'bar'
				)
			),
			'object method call' => array(
				new Expr\MethodCall(
					new Expr\Variable('this'), 'foo'
				)
			),
			'static method call' => array(
				new Expr\StaticCall(
					new Name('Foo'), 'bar'
				)
			)
		);
	}

	/**
	 * @param Expr $variable
	 *
	 * @test
	 * @dataProvider watchableExpressionsProvider
	 */
	public function expressionFromComparisonIsAddedAsWatcher($variable) {
		$ifNode = new Stmt\If_(new Expr\BinaryOp\Equal($variable, new LNumber(5)));
		$mockedAnalysis = $this->mockFileAnalysis();

		$mockedBreakpoint = $this->mockBreakpoint();
		$mockedBreakpoint->expects($this->once())->method('addWatchedExpression')->with($this->identicalTo($variable));
		$subject = $this->mockBreakpointFactory($mockedAnalysis, array($mockedBreakpoint));

		/** @var $subject BreakpointFactory */
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
	 * @param Breakpoint[] $breakpoints
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockBreakpointFactory($mockedAnalysis, $breakpoints) {
		$subject = $this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\BreakpointFactory')
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
	protected function mockBreakpoint() {
		$mockedBreakpoint = $this->getMockBuilder('AndreasWolf\DecisionCoverage\StaticAnalysis\Breakpoint')
			->disableOriginalConstructor()->getMock();

		return $mockedBreakpoint;
	}

}

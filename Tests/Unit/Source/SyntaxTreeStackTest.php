<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Source;

use AndreasWolf\DecisionCoverage\Event\SyntaxTreeIteratorEvent;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\SyntaxTreeStack;
use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use PhpParser\Node;


class SyntaxTreeStackTest extends UnitTestCase {

	public function syntaxTreeEventProvider() {
		return array(
			'class' => array(
				new Node\Stmt\Class_('ClassName'),
				'syntaxtree.class',
			),
			'class method' => array(
				new Node\Stmt\ClassMethod('classMethodName'),
				'syntaxtree.classmethod',
			),
			'function' => array(
				new Node\Stmt\Function_('functionName'),
				'syntaxtree.function',
			)
		);
	}

	/**
	 * @test
	 * @dataProvider syntaxTreeEventProvider
	 */
	public function enteredEventForNodeTriggersEnteredEventForCorrespondingStructureElement($node, $eventPrefix) {
		$mockedIterator = $this->mockIterator($node);
		$mockedDispatcher = $this->mockDispatcherWithEvents($eventPrefix . '.entered');

		$event = new SyntaxTreeIteratorEvent($mockedIterator);

		$subject = new SyntaxTreeStack($mockedDispatcher);
		$subject->levelEnterHandler($event);
	}

	/**
	 * @test
	 * @dataProvider syntaxTreeEventProvider
	 */
	public function leftEventForNodeTriggersLeftEventForCorrespondingStructureElement($node, $eventPrefix) {
		$mockedIterator = $this->mockIterator($node);
		$mockedDispatcher = $this->mockDispatcherWithEvents(array($eventPrefix . '.entered', $eventPrefix . '.left'));

		$event = new SyntaxTreeIteratorEvent($mockedIterator);

		$subject = new SyntaxTreeStack($mockedDispatcher);
		$subject->levelEnterHandler($event);
		$subject->levelLeftHandler($event);
	}

	/**
	 * @param Node $node
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockIterator($node) {
		$mockedIterator = $this->getMockBuilder('AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator')
			->disableOriginalConstructor()->getMock();
		$mockedIterator->expects($this->any())->method('current')->willReturn($node);

		return $mockedIterator;
	}

	/**
	 * @param string|array $expectedEvents
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockDispatcherWithEvents($expectedEvents) {
		$mockedDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
			->getMock();
		if (is_array($expectedEvents)) {
			foreach ($expectedEvents as $key => $event) {
				$mockedDispatcher->expects($this->at($key))->method('dispatch')->with($this->equalTo($event));
			}
		} else {
			$mockedDispatcher->expects($this->once())->method('dispatch')->with($this->equalTo($expectedEvents));
		}

		return $mockedDispatcher;
	}

}

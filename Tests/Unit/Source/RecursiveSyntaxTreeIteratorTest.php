<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Source;

use AndreasWolf\DecisionCoverage\Event\SyntaxTreeIteratorEvent;
use AndreasWolf\DecisionCoverage\Source\RecursiveSyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\Source\SyntaxTreeIterator;
use AndreasWolf\DecisionCoverage\Tests\ParserBasedTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class RecursiveSyntaxTreeIteratorTest extends ParserBasedTestCase {

	/**
	 * @test
	 */
	public function noEventsAreTriggeredForLinearNodeSequence() {
		$nodes = $this->parseCode('$foo; "bar"; 5; someMethod();');
		$mockedDispatcher = $this->mockEventDispatcher();
		$mockedDispatcher->expects($this->never())->method('dispatch');

		$subject = new RecursiveSyntaxTreeIterator(new SyntaxTreeIterator($nodes, TRUE), $mockedDispatcher,
			\RecursiveIteratorIterator::SELF_FIRST);

		$i = 0;
		foreach ($subject as $node) {
			++$i;
		}
		$this->assertEquals(4, $i);
	}

	/**
	 * @test
	 */
	public function ifStatementTriggersEventsForConditionNodes() {
		$nodes = $this->parseCode('if ($foo) {}');
		$mockedDispatcher = $this->mockEventDispatcher();
		$mockedDispatcher->expects($this->at(0))->method('dispatch')->with('syntaxtree.level.entered');
		$mockedDispatcher->expects($this->at(1))->method('dispatch')->with('syntaxtree.level.left');

		$subject = new RecursiveSyntaxTreeIterator(new SyntaxTreeIterator($nodes, TRUE), $mockedDispatcher,
			\RecursiveIteratorIterator::SELF_FIRST);

		foreach ($subject as $node) {
		}
	}

	/**
	 * @test
	 */
	public function currentSyntaxTreeItemCanBeFetchedFromLevelEnteredEvent() {
		$nodes = $this->parseCode('if ($foo) {}');
		$dispatcher = new EventDispatcher();
		$dispatcher->addListener('syntaxtree.level.entered', function(SyntaxTreeIteratorEvent $event) {
			$this->assertInstanceOf('PhpParser\Node', $event->getIterator()->current());
		});

		$subject = new RecursiveSyntaxTreeIterator(new SyntaxTreeIterator($nodes, TRUE), $dispatcher,
			\RecursiveIteratorIterator::SELF_FIRST);

		foreach ($subject as $node) {
		}
	}

	/**
	 * @test
	 */
	public function iteratorIsNotValidForLevelLeftEvent() {
		$nodes = $this->parseCode('if ($foo) {}');
		$dispatcher = new EventDispatcher();
		$dispatcher->addListener('syntaxtree.level.left', function(SyntaxTreeIteratorEvent $event) {
			$this->assertFalse($event->getIterator()->valid());
		});

		$subject = new RecursiveSyntaxTreeIterator(new SyntaxTreeIterator($nodes, TRUE), $dispatcher,
			\RecursiveIteratorIterator::SELF_FIRST);

		foreach ($subject as $node) {
		}
	}

	protected function mockEventDispatcher() {
		return $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
	}

}

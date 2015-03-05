<?php
namespace AndreasWolf\DecisionCoverage\Tests\Functional\Report;

use AndreasWolf\DebuggerClient\Tests\Functional\FunctionalTestCase;
use AndreasWolf\DecisionCoverage\Coverage\ClassCoverage;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Report\Html\SourceFile;
use AndreasWolf\DecisionCoverage\Report\ProjectXmlReportBuilder;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;


class ProjectXmlReportBuilderTest extends FunctionalTestCase {

	/**
	 * @test
	 */
	public function fileWithSingleEmptyClassCoverageYieldsCorrectXmlStructure() {
		$fileCoverage = $this->createFileCoverage('');
		$fileCoverage->addCoverage($this->createClassCoverage('ClassA'));

		$subject = new ProjectXmlReportBuilder(new \SplFileInfo('/tmp'));
		$subject->handleFileCoverage($fileCoverage);
		$reportXml = $subject->getXmlDocument();

		$this->assertEqualXMLStructure(
			$reportXml->documentElement,
			dom_import_simplexml(simplexml_load_string(
				'<coverage>
					<file>
						<input-coverage />
						<class>
							<input-coverage />
						</class>
					</file>
				</coverage>'
			))
		);
	}

	/**
	 * @test
	 */
	public function fileWithClassCoverageWithOneMethodYieldsCorrectXmlStructure() {
		$classCoverage = $this->createClassCoverage('ClassA');
		$classCoverage->addMethodCoverage($this->createMethodCoverage('methodB'));
		$fileCoverage = $this->createFileCoverage();
		$fileCoverage->addCoverage($classCoverage);

		$subject = new ProjectXmlReportBuilder(new \SplFileInfo('/tmp'));
		$subject->handleFileCoverage($fileCoverage);
		$reportXml = $subject->getXmlDocument();

		$this->assertEqualXMLStructure(
			$reportXml->documentElement,
			dom_import_simplexml(simplexml_load_string(
				'<coverage>
					<file>
						<input-coverage />
						<class>
							<input-coverage />
							<method>
								<input-coverage />
							</method>
						</class>
					</file>
				</coverage>'
			))
		);
	}

	/**
	 * @test
	 */
	public function fileWithTwoClassCoveragesWithDifferentMethodCountsYieldsCorrectXmlStructure() {
		$classCoverageA = $this->createClassCoverage('ClassA');
		$classCoverageA->addMethodCoverage($this->createMethodCoverage('methodB'));
		$classCoverageA->addMethodCoverage($this->createMethodCoverage('methodC'));
		$classCoverageB = $this->createClassCoverage('ClassB');
		$classCoverageB->addMethodCoverage($this->createMethodCoverage('methodD'));
		$fileCoverage = $this->createFileCoverage();
		$fileCoverage->addCoverage($classCoverageA);
		$fileCoverage->addCoverage($classCoverageB);

		$subject = new ProjectXmlReportBuilder(new \SplFileInfo('/tmp'));
		$subject->handleFileCoverage($fileCoverage);
		$reportXml = $subject->getXmlDocument();

		$this->assertEqualXMLStructure(
			$reportXml->documentElement,
			dom_import_simplexml(simplexml_load_string(
				'<coverage>
					<file>
						<input-coverage />
						<class>
							<input-coverage />
							<method>
								<input-coverage />
							</method>
							<method>
								<input-coverage />
							</method>
						</class>
						<class>
							<input-coverage />
							<method>
								<input-coverage />
							</method>
						</class>
					</file>
				</coverage>'
			))
		);
	}

	/**
	 * @param string
	 * @return Class_
	 */
	protected function mockClass($name) {
		/** @var Class_ $mock */
		$mock = $this->getMockBuilder(Class_::class)->disableOriginalConstructor()->getMock();
		$mock->name = $name;

		return $mock;
	}

	/**
	 * @param string
	 * @return ClassMethod
	 */
	protected function mockClassMethod($name) {
		/** @var ClassMethod $mock */
		$mock = $this->getMockBuilder(ClassMethod::class)->disableOriginalConstructor()->getMock();
		$mock->name = $name;

		return $mock;
	}

	/**
	 * @param string $class
	 * @return ClassCoverage
	 */
	protected function createClassCoverage($class) {
		return new ClassCoverage($this->mockClass($class));
	}

	/**
	 * @param string $method
	 * @return MethodCoverage
	 */
	protected function createMethodCoverage($method) {
		return new MethodCoverage($this->mockClassMethod($method));
	}

	/**
	 * @return FileCoverage
	 */
	protected function createFileCoverage() {
		return new FileCoverage('/tmp/' . uniqid());
	}

}

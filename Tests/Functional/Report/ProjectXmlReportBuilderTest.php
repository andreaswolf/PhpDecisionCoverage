<?php
namespace AndreasWolf\DecisionCoverage\Tests\Functional\Report;

use AndreasWolf\DebuggerClient\Tests\Functional\FunctionalTestCase;
use AndreasWolf\DecisionCoverage\Coverage\ClassCoverage;
use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;
use AndreasWolf\DecisionCoverage\Coverage\MethodCoverage;
use AndreasWolf\DecisionCoverage\Report\ProjectXmlReportBuilder;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use TheSeer\fDOM\fDOMDocument;


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
	public function filePathsAndClassAndMethodNamesAreAddedAsNodeAttributes() {
		$methodCoverage = $this->createMethodCoverage('methodA');
		$classCoverage = $this->createClassCoverage('ClassA');
		$classCoverage->addMethodCoverage($methodCoverage);
		$fileCoverage = $this->createFileCoverage('/path/to/some/file.php');
		$fileCoverage->addCoverage($classCoverage);

		$subject = new ProjectXmlReportBuilder(new \SplFileInfo('/tmp'));
		$subject->handleFileCoverage($fileCoverage);
		$reportXml = $subject->getXmlDocument();

		$this->assertEquals('/path/to/some/file.php', $this->queryXpath('//file[1]/@path', $reportXml)->item(0)->nodeValue);
		$this->assertEquals('ClassA', $this->queryXpath('//file[1]/class[1]/@name', $reportXml)->item(0)->nodeValue);
		$this->assertEquals('methodA', $this->queryXpath('//file[1]/class[1]/method[1]/@name', $reportXml)->item(0)->nodeValue);
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

	protected function queryXpath($xpath, fDOMDocument $dom, \DOMNode $context = NULL) {
		$xpathObj = new \DOMXPath($dom);
		$context = $context === NULL ? $dom->documentElement : $context;
		return $xpathObj->query($xpath, $context);
	}

	/**
	 * @param string
	 * @return Class_
	 */
	protected function mockClass($name) {
		/** @var Class_ $mock */
		$mock = new Class_($name);

		return $mock;
	}

	/**
	 * @param string
	 * @return ClassMethod
	 */
	protected function mockClassMethod($name) {
		/** @var ClassMethod $mock */
		$mock = new ClassMethod($name);

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
	 * @param string $filePath
	 * @return FileCoverage
	 */
	protected function createFileCoverage($filePath = '') {
		if ($filePath == '') {
			$filePath = '/tmp/' . uniqid();
		}

		return new FileCoverage($filePath);
	}

}

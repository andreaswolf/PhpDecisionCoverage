<?php
namespace AndreasWolf\DecisionCoverage\Tests;
use PhpParser\Lexer;
use AndreasWolf\DecisionCoverage\Source\SourceFile;
use PhpParser\Parser;


/**
 * Base test case for dealing with source (files).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
abstract class SourceTestCase extends \PHPUnit_Framework_TestCase {

	protected $files = array();

	public function tearDown() {
		parent::tearDown();
		foreach ($this->files as $file) {
			unlink($file);
		}
	}

	/**
	 * Puts the given code to a temporary file.
	 *
	 * The file is deleted automatically after the tests.
	 *
	 * @param string $code
	 * @return string The file path to the temporary file.
	 */
	protected function putCodeToFile($code) {
		$fileName = tempnam(sys_get_temp_dir(), 'source-coverage-');
		file_put_contents($fileName, '<?php ' . $code);

		$this->files[] = $fileName;

		return $fileName;
	}

	/**
	 * Creates a source file object for the given code.
	 *
	 * @param string $code
	 * @return SourceFile
	 */
	protected function createSourceFileForCode($code) {
		$sourceFileName = $this->putCodeToFile($code);
		$sourceFile = new SourceFile($sourceFileName);
		$sourceFile->setParser(new Parser(new Lexer()));

		return $sourceFile;
	}
}

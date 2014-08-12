<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit;

use PhpParser\Lexer;
use PhpParser\Parser;


/**
 * Base test case for all tests that need a working PHP parser instance
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ParserBasedTestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Parser
	 */
	protected $parser;

	protected function setUp() {
		parent::setUp();

		$this->parser = new Parser(new Lexer());
	}

	protected function parseCode($code) {
		return $this->parser->parse('<?php ' . $code);
	}
}
 
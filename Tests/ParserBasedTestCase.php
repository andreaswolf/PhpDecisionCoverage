<?php
namespace AndreasWolf\DecisionCoverage\Tests;

use PhpParser\Lexer;
use PhpParser\Parser;


/**
 * Base test case for all tests that need a working PHP parser instance
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ParserBasedTestCase extends \PHPUnit_Framework_TestCase {
	use ParserTestIntegration;

}

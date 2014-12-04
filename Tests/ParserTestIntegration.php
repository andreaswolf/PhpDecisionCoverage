<?php
namespace AndreasWolf\DecisionCoverage\Tests;


use PhpParser\Lexer;
use PhpParser\Parser;


trait ParserTestIntegration {

	/**
	 * @var Parser
	 */
	protected $parser;

	/**
	 * @before
	 */
	public function __parserSetup() {
		$this->parser = new Parser(new Lexer());
	}

	protected function parseCode($code) {
		if (!$this->parser) {
			$this->__parserSetup();
		}
		return $this->parser->parse('<?php ' . $code);
	}

}
 
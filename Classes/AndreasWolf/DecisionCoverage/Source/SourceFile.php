<?php
namespace AndreasWolf\DecisionCoverage\Source;

use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;


/**
 * Abstraction for a source file.
 *
 * This class has to be used instead of directly parsing the file, because the parser does not have any reference to
 * the file, but we need it for setting the breakpoints.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SourceFile {

	/**
	 * The absolute file path
	 *
	 * @var string
	 */
	protected $filePath;

	/**
	 * The statements parsed from the file
	 *
	 * @var Node[]
	 */
	protected $statements;

	/**
	 * @var Parser
	 */
	protected $parser;

	public function __construct($filePath) {
		$this->filePath = $filePath;
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}

	/**
	 * @param \PhpParser\Parser $parser
	 */
	public function setParser($parser) {
		$this->parser = $parser;
	}

	/**
	 * @return void
	 */
	protected function parseFile() {
		$this->statements = $this->parser->parse(file_get_contents($this->filePath));
	}

	/**
	 * Returns the statements at the topmost level of this file, e.g. use statements and class declarations.
	 *
	 * @return \PhpParser\Node[]
	 */
	public function getTopLevelStatements() {
		if ($this->statements === NULL) {
			$this->parseFile();
		}
		return $this->statements;
	}

}

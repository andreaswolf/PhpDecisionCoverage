<?php
namespace AndreasWolf\DecisionCoverage\Source;

use PhpParser\Node;


/**
 * Filter interface for abstract syntax trees.
 *
 * Use this to e.g. extract a part of an
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface SyntaxTreeFilter {

	/**
	 * Walks the AST of the given source file.
	 *
	 * @param SourceFile $sourceFile
	 * @return array The filtered list of nodes
	 */
	public function walkSourceFile(SourceFile $sourceFile);

	/**
	 * Walks the given AST.
	 *
	 * @param Node|Node[] $nodes
	 * @return array The filtered list of nodes
	 */
	public function walkTree($nodes);

}

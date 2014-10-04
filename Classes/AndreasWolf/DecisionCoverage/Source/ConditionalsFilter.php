<?php
namespace AndreasWolf\DecisionCoverage\Source;
use PhpParser\Node\Stmt;
use PhpParser\Node;


/**
 * Filters out all conditionals from an AST. Stores the context (class + method/function) for each conditional
 * statement.
 *
 * Currently, only if statements are supported. Support for other types will be added in the future.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ConditionalsFilter {

	protected $currentFile = NULL;

	protected $currentClass = NULL;

	protected $currentMethod = NULL;

	/**
	 * @var StatementContext
	 */
	protected $currentContext;


	/**
	 * Walks the AST of the given source file and returns a list of all conditionals.
	 *
	 * @param SourceFile $sourceFile
	 * @return array
	 */
	public function walkSourceFile(SourceFile $sourceFile) {
		$this->currentFile = $sourceFile->getFilePath();
		$this->updateContext();

		$conditionals = $this->walkTree($sourceFile->getTopLevelStatements());
		$this->currentFile = NULL;

		return $conditionals;
	}

	/**
	 * Walks the given AST and returns a list of all conditionals.
	 *
	 * @param Node|Node[] $nodes
	 * @return array
	 */
	public function walkTree($nodes) {
		if (!is_array($nodes)) {
			$nodes = array($nodes);
		}
		$treeIterator = new \RecursiveIteratorIterator(new SyntaxTreeIterator($nodes), \RecursiveIteratorIterator::SELF_FIRST);

		// FIXME this code is not able to correctly handle statements that are not part of a function
		$conditionals = array();
		/** @var Node $node */
		foreach ($treeIterator as $node) {
			switch ($node->getType()) {
				case 'Stmt_Class':
					$this->currentClass = $node;
					// there can be no conditional only in a class, so we’ll find a method first -> don’t update the
					// context here
					break;

				case 'Stmt_ClassMethod':
					$this->currentMethod = $node;
					$this->updateContext();
					break;

				case 'Stmt_Function':
					$this->currentMethod = $node;
					$this->currentClass = NULL;
					$this->updateContext();
					break;

				case 'Stmt_If':
					$conditionals[] = $this->createIfNode($node);
					break;
			}
		}

		return $conditionals;
	}

	/**
	 * @return void
	 */
	protected function updateContext() {
		$this->currentContext = new StatementContext($this->currentFile, $this->currentMethod, $this->currentClass);
	}

	protected function createIfNode(Stmt $node) {
		$conditionalNode = new IfStatement($node, $this->currentContext);

		return $conditionalNode;
	}

}

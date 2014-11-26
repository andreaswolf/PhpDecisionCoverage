<?php
namespace AndreasWolf\DecisionCoverage\Source;

use PhpParser\Node\Expr;
use PhpParser\Node;
use RecursiveIterator;


/**
 * Iterator for an AST.
 *
 * When used in combination with a RecursiveIteratorIterator instance, this can be used to traverse the complete AST in
 * one go:
 *
 * new \RecursiveIteratorIterator(new SyntaxTreeIterator($nodes), \RecursiveIteratorIterator::SELF_FIRST);
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SyntaxTreeIterator implements \RecursiveIterator {

	/**
	 * @var int
	 */
	protected $cursor = 0;

	/**
	 * @var array
	 */
	protected $nodes;

	/**
	 * @var bool
	 */
	protected $includeAllSubNodes;

	protected $subnodeInclusionOrder = array(
		'Stmt_If' => array('cond', 'stmts', 'elseifs', 'else'),
		'Stmt_ElseIf' => array('cond', 'stmts'),
		'Stmt_Echo' => array('exprs'),
		'Expr_BinaryOp' => array('left', 'right'),
		'Expr_MethodCall' => array('var'),
		'Expr_PropertyFetch' => array('var'),
	);

	/**
	 * @param Node[]|Node $nodes
	 * @param bool $includeAllSubNodes If TRUE, all sub nodes (e.g. conditions, elseifs and else for the if-statement) will be included
	 */
	public function __construct($nodes, $includeAllSubNodes = FALSE) {
		if (!is_array($nodes)) {
			$nodes = array($nodes);
		}
		$this->nodes = $nodes;
		$this->includeAllSubNodes = $includeAllSubNodes;
	}

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return Node
	 */
	public function current() {
		return $this->nodes[$this->cursor];
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		++$this->cursor;
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return $this->cursor;
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return $this->cursor < count($this->nodes);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->cursor = 0;
	}

	/**
	 * Returns if an iterator can be created for the current entry.
	 *
	 * @link http://php.net/manual/en/recursiveiterator.haschildren.php
	 * @return bool true if the current entry can be iterated over, otherwise returns false.
	 */
	public function hasChildren() {
		if (!$this->valid()) {
			return FALSE;
		}

		if ($this->includeAllSubNodes === TRUE) {
			return TRUE;
		} else {
			$subNodeNames = $this->current()->getSubNodeNames();
			return in_array('stmts', $subNodeNames) && count($this->current()->stmts) > 0;
		}
	}

	/**
	 * Returns an iterator for the current entry.
	 *
	 * @link http://php.net/manual/en/recursiveiterator.getchildren.php
	 * @return RecursiveIterator An iterator for the current entry.
	 */
	public function getChildren() {
		return new self($this->getCurrentNodeSubnodes(), $this->includeAllSubNodes);
	}

	/**
	 * @return array
	 */
	protected function getCurrentNodeSubnodes() {
		if (!$this->valid()) {
			return array();
		}
		$subnodeNames = $this->current()->getSubNodeNames();

		$subnodes = array();
		if ($this->includeAllSubNodes === TRUE) {
			$this->addCurrentNodeSubnodes($subnodes);
		} else {
			if (in_array('stmts', $subnodeNames)) {
				$subnodes = $this->current()->stmts;
			}
		}

		return $subnodes;
	}

	/**
	 * @param array $subnodes
	 */
	protected function addCurrentNodeSubnodes(&$subnodes) {
		$currentNode = $this->current();
		$currentNodeType = $currentNode->getType();
		$subnodeInclusionOrder = $this->getSubnodeInclusionOrderForType($currentNodeType);

		if (count($subnodeInclusionOrder) == 0) {
			// include statements and expressions if no specific order is defined
			$subNodeNames = $currentNode->getSubNodeNames();
			$subnodeInclusionOrder = array_intersect($subNodeNames, array('stmts', 'exprs'));
		}
		foreach ($subnodeInclusionOrder as $subnodeType) {
			$currentSubnodes = $currentNode->$subnodeType;
			if (!$currentSubnodes) {
				continue;
			}
			if (is_array($currentSubnodes)) {
				$subnodes = array_merge($subnodes, $currentSubnodes);
			} else {
				$subnodes[] = $currentSubnodes;
			}
		}
	}

	/**
	 * Returns the order in which the subnodes of th specified type should be traversed
	 *
	 * @param string $type
	 * @return array
	 */
	protected function getSubnodeInclusionOrderForType($type) {
		// direct hit
		if (isset($this->subnodeInclusionOrder[$type])) {
			return $this->subnodeInclusionOrder[$type];
		}

		// try to find a shorter super-type, e.g. Expr_Binary for Expr_Binary_Equal
		while (substr_count($type, '_') >= 2) {
			$type = substr($type, 0, strrpos($type, '_'));
			if (isset($this->subnodeInclusionOrder[$type])) {
				return $this->subnodeInclusionOrder[$type];
			}
		}

		return array();
	}


}
 
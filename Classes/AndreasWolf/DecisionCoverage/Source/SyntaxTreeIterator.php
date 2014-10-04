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
 * new \RecursiveIteratorIterator(new SyntaxTreeIterator($nodes), RecursiveIteratorIterator::SELF_FIRST);
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
	 * @param Node[] $nodes
	 */
	public function __construct(array $nodes) {
		$this->nodes = $nodes;
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

		$subNodeNames = $this->current()->getSubNodeNames();
		return in_array('stmts', $subNodeNames);
	}

	/**
	 * Returns an iterator for the current entry.
	 *
	 * @link http://php.net/manual/en/recursiveiterator.getchildren.php
	 * @return RecursiveIterator An iterator for the current entry.
	 */
	public function getChildren() {
		return new self($this->getCurrentNodeSubnodes());
	}

	/**
	 * @return array
	 */
	protected function getCurrentNodeSubnodes() {
		if (!$this->valid()) {
			return array();
		}
		$subnodeNames = $this->current()->getSubNodeNames();
		if (in_array('stmts', $subnodeNames)) {
			return $this->current()->stmts;
		} else {
			return array();
		}
	}


}
 
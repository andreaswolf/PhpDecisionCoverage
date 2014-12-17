<?php
namespace AndreasWolf\DecisionCoverage\Source;


/**
 * Iterator to traverse a decision and its sub-decisions and conditions.
 *
 * This exposes all left and right sides of binary operations, but not e.g. variable access within a property fetch
 * operation.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DecisionIterator extends SyntaxTreeIterator {

	/**
	 * This iterator should only support binary operations.
	 *
	 * @var array
	 */
	protected $subnodeInclusionOrder = array(
		'Expr_BinaryOp_BooleanAnd' => array('left', 'right'),
		'Expr_BinaryOp_BooleanOr' => array('left', 'right'),
		'Expr_BinaryOp' => array(),
	);

	/**
	 * Returns an iterator for the current entry.
	 *
	 * @link http://php.net/manual/en/recursiveiterator.getchildren.php
	 * @return \RecursiveIterator An iterator for the current entry.
	 */
	public function getChildren() {
		return new self($this->getCurrentNodeSubnodes(), $this->includeAllSubNodes);
	}

}

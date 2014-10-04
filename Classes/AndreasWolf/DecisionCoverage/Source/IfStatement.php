<?php
namespace AndreasWolf\DecisionCoverage\Source;
use AndreasWolf\DecisionCoverage\BooleanLogic\BooleanCondition;
use PhpParser\Node\Stmt;


/**
 * Abstraction of an if-statement.
 *
 * Each if-statement has 1..n condition blocks, which are evaluated in their specified order
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class IfStatement extends DecisionStatement {

	/**
	 * @var BooleanCondition[]
	 */
	protected $conditionBlocks = array();

	/**
	 * @var bool
	 */
	protected $hasElseBlock = FALSE;

	public function __construct(Stmt $decision, StatementContext $context) {
		// TODO check if $conditional really is an if-statement
		parent::__construct($decision, $context);

		$this->extractConditionBlocks();
	}

	/**
	 * Extracts the single conditions from the if/else if/else blocks.
	 */
	protected function extractConditionBlocks() {
		$this->conditionBlocks[] = new BooleanCondition($this->conditional);

		foreach ($this->conditional->elseifs as $elseIf) {
			$this->conditionBlocks[] = new BooleanCondition($elseIf);
		}

		$this->hasElseBlock = is_object($this->conditional->else);
	}

	/**
	 * Returns the number of condition blocks, in other words the number of elseif-statements - 1.
	 *
	 * @return int
	 */
	public function getConditionBlockCount() {
		return count($this->conditionBlocks);
	}

	/**
	 * Returns the condition block with the given index.
	 *
	 * 0 = if-block, 1+ = elseif blocks
	 *
	 * @param int $index
	 * @return BooleanCondition
	 */
	public function getConditionBlock($index) {
		return $this->conditionBlocks[$index];
	}

	/**
	 * Returns all condition blocks from the if and elseif statements.
	 *
	 * @return BooleanCondition[]
	 */
	public function getConditionBlocks() {
		return $this->conditionBlocks;
	}

	public function hasElseBlock() {
		return $this->hasElseBlock;
	}

}

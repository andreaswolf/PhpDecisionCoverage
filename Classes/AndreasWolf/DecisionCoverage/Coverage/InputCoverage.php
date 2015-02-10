<?php
namespace AndreasWolf\DecisionCoverage\Coverage;

use PhpParser\Node\Expr;


interface InputCoverage {

	/**
	 * @return Expr
	 */
	public function getExpression();

	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @return int
	 */
	public function countFeasibleInputs();

	/**
	 * @return int
	 */
	public function countUniqueCoveredInputs();

}
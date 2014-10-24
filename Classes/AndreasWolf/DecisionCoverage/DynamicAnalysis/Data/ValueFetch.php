<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use PhpParser\Node\Expr;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use React\Promise\Deferred;


/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class ValueFetch extends Deferred {

	/**
	 * The expression the value should be fetched for.
	 *
	 * @var Expr
	 */
	protected $expression;

	/**
	 * @var PrettyPrinterAbstract
	 */
	static protected $printer;


	public function __construct(Expr $expression) {
		$this->expression = $expression;
	}

	/**
	 * @return Expr
	 */
	public function getExpression() {
		return $this->expression;
	}

	protected static function getExpressionPrinter() {
		if (!self::$printer) {
			self::$printer = new Standard();
		}
		return self::$printer;
	}

	/**
	 * Returns the expression as a string (that can be evaluated by PHP)
	 *
	 * @return string
	 */
	public function getExpressionAsString() {
		return self::getExpressionPrinter()->prettyPrintExpr($this->expression);
	}

}

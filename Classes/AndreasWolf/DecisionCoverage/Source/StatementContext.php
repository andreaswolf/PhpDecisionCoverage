<?php
namespace AndreasWolf\DecisionCoverage\Source;
use PhpParser\Node\Stmt;


/**
 * The context (class, method) a statement is in.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class StatementContext {

	/**
	 * @var Stmt\Class_
	 */
	protected $class;

	/**
	 * @var Stmt\ClassMethod|Stmt\Function_
	 */
	protected $function;

	/**
	 * @var string
	 */
	protected $file;


	public function __construct($file, Stmt $function = NULL, Stmt\Class_ $class = NULL) {
		// TODO check if the function is an instance of either ClassMethod or Function_
		$this->file = $file;
		$this->function = $function;
		$this->class = $class;

		if ($this->class) {
			$reflection = new \ReflectionClass($this->class);
		}
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->file;
	}

	/**
	 * @return bool
	 */
	public function isInClass() {
		return $this->class !== NULL;
	}

	/**
	 * @return string
	 */
	public function getClassName() {
		return $this->class->name;
	}

	/**
	 * @return string
	 */
	public function getFunctionName() {
		return $this->function->name;
	}

}

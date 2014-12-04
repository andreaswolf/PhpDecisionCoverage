<?php
namespace AndreasWolf\DecisionCoverage\Tests\Helpers\PhpParser;

use AndreasWolf\DecisionCoverage\Tests\Helpers\PhpUnit\InvocationMatcher;
use PhpParser\Node\Expr;


/**
 * Builder for PHP syntax tree expression mocks
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ExpressionMockBuilder {

	/**
	 * @var string
	 */
	protected $class;

	/**
	 * @var array
	 */
	protected $mockedAttributes;


	public function __construct($class) {
		$this->class = $class;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return ExpressionMockBuilder
	 */
	public function addAttribute($name, $value) {
		// use a structure optimized for PHPUnit’s ReturnValueMap class; the "nulL" is the default for the second
		// parameter of getAttribute(), $default
		$this->mockedAttributes[] = array(
			$name, null, $value
		);

		return $this;
	}

	/**
	 * @return Expr
	 */
	public function getMock() {
		$mockGenerator = new \PHPUnit_Framework_MockObject_Generator();
		/** @var \PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $mockGenerator->getMock($this->class);

		$mock->expects(InvocationMatcher::any())->method('getAttribute')->willReturnMap($this->mockedAttributes);

		return $mock;
	}

}
 
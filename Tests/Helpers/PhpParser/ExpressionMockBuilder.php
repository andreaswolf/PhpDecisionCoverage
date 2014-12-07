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

	/**
	 * @var Expr
	 */
	protected $mock;


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
	 * @param string $mockedId
	 * @return $this
	 */
	public function setId($mockedId) {
		$this->addAttribute('coverage__nodeId', $mockedId);

		return $this;
	}

	/**
	 * @return Expr
	 */
	public function getMock() {
		if (!$this->mock) {
			$mockGenerator = new \PHPUnit_Framework_MockObject_Generator();
			/** @var \PHPUnit_Framework_MockObject_MockObject $mock */
			$this->mock = $mockGenerator->getMock($this->class);

			$this->mock->expects(InvocationMatcher::any())->method('getAttribute')->willReturnMap($this->mockedAttributes);
		}

		return $this->mock;
	}

}
 
<?php
namespace AndreasWolf\DecisionCoverage\Tests\Helpers\PhpUnit;


class InvocationMatcher {

	public static function any() {
		return new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount();
	}

}

<?php
namespace AndreasWolf\DecisionCoverage\DynamicAnalysis\Data;

use AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\Test;


interface Sample {

	public function setTest(Test $test);

	public function getTest();

}

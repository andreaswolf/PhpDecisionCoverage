<?php
namespace AndreasWolf\DecisionCoverage\Config;

class ConfigLoaderException extends \Exception {

	const NOT_FOUND = 1;
	const INVALID_NAMESPACE = 2;
	const PARSE_ERROR = 3;

}

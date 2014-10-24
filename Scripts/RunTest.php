#!/usr/bin/env php
<?php
/**
 * Runner script for tests that communicate back to a running Decision Coverage data collection process
 * via a fifo queue that is passed as an argument (--fifo).
 *
 * This is called from the "run" command (RunTestsCommand class); see there for more information
 *
 * Parts of this (the PHPUnit bootstrap) are © Sebastian Bergmann.
 */

foreach (array(__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        define('PHPUNIT_COMPOSER_INSTALL', $file);
        break;
    }
}

unset($file);

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    echo 'You need to set up the project dependencies using the following commands:' . PHP_EOL .
        'wget http://getcomposer.org/composer.phar' . PHP_EOL .
        'php composer.phar install' . PHP_EOL;
    die(1);
}

require PHPUNIT_COMPOSER_INSTALL;

\AndreasWolf\DecisionCoverage\Core\Bootstrap::getInstance()->setupAutoloader();


$arguments = $_SERVER['argv'];

$communicationFifoArgumentPosition = array_search('--fifo', $arguments);
if ($communicationFifoArgumentPosition === FALSE) {
	throw new RuntimeException('Parameter --fifo not given');
}
list(, $fifoFile) = array_splice($arguments, $communicationFifoArgumentPosition, 2);

echo "Running tests\n";

$command = new \AndreasWolf\DecisionCoverage\DynamicAnalysis\PhpUnit\TestCommand($fifoFile);
$command->run($arguments, TRUE);

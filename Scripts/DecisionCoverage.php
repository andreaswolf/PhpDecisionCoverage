#!/usr/bin/env php
<?php
require __DIR__ . '/../Classes/AndreasWolf/DecisionCoverage/Core/Bootstrap.php';

\AndreasWolf\DecisionCoverage\Core\Bootstrap::getInstance()->run();

$application = new \Symfony\Component\Console\Application();
$application->setName('Decision Coverage Tool');
$application->setVersion('1.0.0');
$application->addCommands(array(
	new \AndreasWolf\DecisionCoverage\Console\RunTestsCommand(),
	new \AndreasWolf\DecisionCoverage\Console\AnalyzeCommand(),
	new \AndreasWolf\DecisionCoverage\Console\BuildCoverageCommand(),
	new \AndreasWolf\DecisionCoverage\Console\CoverageCommand(),
));
$application->run();

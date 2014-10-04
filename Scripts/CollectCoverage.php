<?php

require __DIR__ . '/../Classes/AndreasWolf/DecisionCoverage/Core/Bootstrap.php';

// we have to run both bootstraps currently so the debugger client’s event dispatcher gets instantiated correctly
\AndreasWolf\DecisionCoverage\Core\Bootstrap::getInstance()->run();
\AndreasWolf\DebuggerClient\Core\Bootstrap::getInstance()->run();

$debuggerClient = new \AndreasWolf\DebuggerClient\Core\Client();
$debuggerClient->addSubscriber(
	new \AndreasWolf\DecisionCoverage\DynamicAnalysis\Debugger\ClientEventSubscriber($debuggerClient)
);
$debuggerClient->run();

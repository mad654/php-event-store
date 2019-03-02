#!/usr/bin/env php

<?php

require __DIR__ . '/../../../vendor/autoload.php';

use mad654\eventstore\example\cli\ExampleLighterHistoryCommand;
use mad654\eventstore\example\cli\ExampleLighterInitCommand;
use mad654\eventstore\example\cli\ExampleLighterSwitchCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new ExampleLighterInitCommand("init"));
$application->add(new ExampleLighterSwitchCommand("switch"));
$application->add(new ExampleLighterHistoryCommand("history"));

$application->run();
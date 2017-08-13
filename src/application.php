<?php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \Translation\Converter\Command\ConvertCommand());

$application->run();

<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \Translation\Converter\Command\ConvertCommand());

$application->run();

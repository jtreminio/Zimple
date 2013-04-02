<?php

use jtreminio\Container\Container;

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('jtreminio\Container\Tests', __DIR__);

$pimple = new \Pimple();

$pimple['FooBarDateTime'] = function () {
    return new \DateTime();
};

Container::setPimple($pimple);

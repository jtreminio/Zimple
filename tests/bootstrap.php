<?php

use jtreminio\Zimple\Zimple;

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('jtreminio\Zimple\Tests', __DIR__);

Zimple::setPimple(new \Pimple);

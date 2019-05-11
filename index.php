<?php

require_once __DIR__ . '/vendor/autoload.php';

$bootstrap = new \Cundd\Noshi\Bootstrap(getenv('BASE_PATH') ?: __DIR__);
$bootstrap->run();

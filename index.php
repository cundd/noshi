<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$bootstrap = new \Cundd\Noshi\Bootstrap(getenv('BASE_PATH') ?: __DIR__);
$bootstrap->run();

<?php

error_reporting(E_ALL);
ini_set('default_charset', 'utf-8');
ini_set('mbstring.encoding_translation', true);
ini_set('mbstring.func_overload', 6);

require_once __DIR__ . '/vendor/autoload.php';

$bootstrap = new \Cundd\Noshi\Bootstrap(__DIR__);
$bootstrap->run();

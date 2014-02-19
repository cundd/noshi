<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:12
 */

error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

$bootstrap = new \Cundd\Noshi\Bootstrap(__DIR__);
$bootstrap->run();
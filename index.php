<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:12
 */

error_reporting(E_ALL);
ini_set('default_charset', 'utf-8');
ini_set('mbstring.internal_encoding', 'utf-8');
ini_set('mbstring.http_output', 'UTF-8');
ini_set('mbstring.encoding_translation', TRUE);
ini_set('mbstring.func_overload', 6);

require_once __DIR__ . '/vendor/autoload.php';

$bootstrap = new \Cundd\Noshi\Bootstrap(__DIR__);
$bootstrap->run();
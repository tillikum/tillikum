<?php

use Zend\Loader\StandardAutoloader;

error_reporting(E_ALL | E_STRICT);

require dirname(__DIR__) . '/library/autoload.php';

$autoloaderConfig = array(
    'namespaces' => array(
        'TillikumTest' => __DIR__ . '/TillikumTest',
    ),
);

if (defined('TILLIKUMX_PATH')) {
    $autoloaderConfig['namespaces']['TillikumX'] = TILLIKUMX_PATH . '/TillikumX';
}

if (defined('TILLIKUMX_TEST_PATH')) {
    $autoloaderConfig['namespaces']['TillikumXTest'] = TILLIKUMX_TEST_PATH . '/TillikumXTest';
}

$autoloader = new StandardAutoloader($autoloaderConfig);
$autoloader->register();

unset($autoloaderConfig);
unset($autoloader);

<?php

$__config__ = array(
    'bootstrap' => array(
        'class' => 'Tillikum\Bootstrap',
        'path' => __DIR__ . '/../vendor/tillikum/tillikum-core-module/src/Tillikum/Bootstrap.php',
    ),
    'pluginPaths' => array(
        'Tillikum\Application\Resource\\' => __DIR__ . '/../vendor/tillikum/tillikum-core-module/src/Tillikum/Application/Resource',
    ),
    'resources' => array(
        'doctrine' => include __DIR__ . '/resources.doctrine.config.php',
        'di' => include __DIR__ . '/resources.di.config.php',
        'form' => include __DIR__ . '/resources.form.config.php',
        'frontController' => include __DIR__ . '/resources.frontController.config.php',
        'layout' => include __DIR__ . '/resources.layout.config.php',
        'serviceManager' => include __DIR__ . '/resources.serviceManager.config.php',
        'translate' => include __DIR__ . '/resources.translate.config.php',
        'view' => include __DIR__ . '/resources.view.config.php',
    ),
);

// Merge local configuration if it exists
if (is_readable(__DIR__ . '/local.config.php')) {
    $__config__ = array_replace_recursive(
        $__config__,
        include __DIR__ . '/local.config.php'
    );
}

return $__config__;

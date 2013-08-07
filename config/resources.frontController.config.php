<?php

return array(
    'actionHelperPaths' => array(
        'Tillikum\Controller\Action\Helper\\' => __DIR__ . '/../vendor/tillikum/tillikum-core-module/src/Tillikum/Controller/Action/Helper',
    ),
    'moduleControllerDirectoryName' => 'controllers',
    'moduleDirectory' => __DIR__ . '/../www/application',
    'plugins' => array(
        100 => 'Tillikum\Controller\Plugin\BuiltinAuthentication',
        101 => 'Tillikum\Controller\Plugin\LocaleFromRequest',
    ),
);

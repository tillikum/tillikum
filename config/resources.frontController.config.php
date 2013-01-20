<?php

return array(
    'actionHelperPaths' => array(
        'Tillikum\Controller\Action\Helper\\' => APPLICATION_PATH . '/library/Tillikum/Controller/Action/Helper',
    ),
    'moduleControllerDirectoryName' => 'controllers',
    'moduleDirectory' => APPLICATION_PATH . '/www/application',
    'plugins' => array(
        100 => 'Tillikum\Controller\Plugin\BuiltinAuthentication',
        101 => 'Tillikum\Controller\Plugin\LocaleFromRequest',
    ),
);

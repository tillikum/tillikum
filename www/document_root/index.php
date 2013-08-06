<?php

require dirname(dirname(__DIR__)) . '/autoload.php';

$application = new \Zend_Application(
    '',
    APPLICATION_PATH . '/config/application.config.php'
);
$application->bootstrap();
$application->run();

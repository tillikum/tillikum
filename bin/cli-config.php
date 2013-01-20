<?php

use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\HelperSet;

chdir(dirname(__DIR__));

require 'library/autoload.php';

$application = new \Zend_Application(
    '',
    'config/application.config.php'
);
$application->bootstrap();

$serviceManager = $application->getBootstrap()
    ->getResource('ServiceManager');

// Expected by the `doctrine' command.
$helperSet = new HelperSet(
    array(
        'em' => new EntityManagerHelper(
            $serviceManager->get('EntityManager')
        )
    )
);

unset($application);
unset($serviceManager);

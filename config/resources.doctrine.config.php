<?php

return array(
    'cache' => array(
        'defaultCacheInstance' => 'default',
        'instances' => array(
            'default' => array(
                'adapterClass' => 'Doctrine\Common\Cache\ArrayCache',
                'namespace' => 'Tillikum_',
            ),
        ),
    ),
    'dbal' => array(
        'defaultConnection' => 'default',
        'connections' => array(
            'default' => array(
                'parameters' => array(
                    'charset' => 'utf8',
                    'collate' => 'utf8_unicode_ci',
                    'driver' => 'pdo_mysql',
                    'dbname' => 'tillikum',
                    'host' => 'localhost',
                    'port' => '3306',
                    'user' => 'root',
                    'password' => '',
                    'driverOptions' => array(
                        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8',
                    ),
                ),
                'types' => array(
                    'utcdatetime' => 'Tillikum\DBAL\Types\UTCDateTimeType',
                ),
            ),
            'test' => array(
                'parameters' => array(
                    'driver' => 'pdo_sqlite',
                    'memory' => true,
                ),
                'types' => array(
                    'utcdatetime' => 'Tillikum\DBAL\Types\UTCDateTimeType',
                ),
            ),
        ),
    ),
    'orm' => array(
        'defaultEntityManager' => 'default',
        'entityManagers' => array(
            'default' => array(
                'connection' => 'default',
                'proxy' => array(
                    'autoGenerateClasses' => false,
                    'namespace' => 'TillikumProxy',
                    'dir' => APPLICATION_PATH . '/data/proxies',
                ),
                'metadataDrivers' => array(
                    'annotationRegistry' => array(
                        'annotationFiles' => array(
                            APPLICATION_PATH . '/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php',
                        ),
                    ),
                    'drivers' => array(
                        100 => array(
                            'mappingNamespace' => 'Tillikum\Entity',
                            'mappingDirs' => array(
                                APPLICATION_PATH . '/library/Tillikum/Entity',
                            ),
                        ),
                    ),
                ),
            ),
            'test' => array(
                'connection' => 'test',
                'proxy' => array(
                    'autoGenerateClasses' => false,
                    'namespace' => 'TillikumProxy',
                    'dir' => APPLICATION_PATH . '/data/proxies',
                ),
                'metadataDrivers' => array(
                    'annotationRegistry' => array(
                        'annotationFiles' => array(
                            APPLICATION_PATH . '/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php',
                        ),
                    ),
                    'drivers' => array(
                        100 => array(
                            'mappingNamespace' => 'Tillikum\Entity',
                            'mappingDirs' => array(
                                APPLICATION_PATH . '/library/Tillikum/Entity',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);

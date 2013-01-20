<?php

namespace TillikumTest\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;

class ControllerTestCase extends \Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        $config = include APPLICATION_PATH . '/config/application.config.php';
        $config['resources']['doctrine']['dbal']['defaultConnection'] = 'test';
        $config['resources']['doctrine']['orm']['defaultEntityManager'] = 'test';
        $config['resources']['frontController']['plugins'][100] = 'Tillikum\Controller\Plugin\DummyAuthentication';
        $config['resources']['serviceManager']['di']['instance']['preferences']['Zend\Authentication\Adapter\AdapterInterface'] = 'Tillikum\Authentication\Adapter\Dummy';
        $config['resources']['serviceManager']['di']['instance']['preferences']['Zend\Authentication\Storage\StorageInterface'] = 'Zend\Authentication\Storage\NonPersistent';

        // Expected by ZF in parent::setUp()
        $this->bootstrap = new \Zend_Application(
            '',
            $config
        );

        parent::setUp();

        $em = $this->bootstrap->getBootstrap()
        ->getResource('Doctrine')
        ->getEntityManager();

        $tool = new SchemaTool($em);

        $classes = $em->getMetadataFactory()->getAllMetadata();

        $tool->dropSchema($classes);

        // Want to use $tool->createSchema($classes) but have trouble with sqlite
        $conn = $em->getConnection();
        foreach ($tool->getCreateSchemaSql($classes) as $sql) {
            if (preg_match('/CREATE INDEX (?!IDX_)/', $sql)) {
                continue;
            }

            $conn->executeQuery($sql);
        }

        $loader = new Loader();
        $loader->loadFromDirectory(dirname(__DIR__) . '/Fixture');

        $executor = new ORMExecutor($em);
        $executor->execute($loader->getFixtures(), true);
    }
}

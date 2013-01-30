<?php

return array(
    'definition' => array(
        'class' => array(
            'Zend\Session\Container' => array(
                'methods' => array(
                    '__construct' => array(
                        'name' => array(
                            'type' => false,
                        ),
                    ),
                ),
            ),
            'Zend\Log\Logger' => array(
                'methods' => array(
                    'addWriter' => array(
                        'writer' => array(
                            'type' => 'Zend\Log\Writer\Syslog',
                        ),
                    ),
                ),
            ),
        ),
    ),
    'instance' => array(
        'aliases' => array(
            'Acl' => 'Tillikum\Permissions\Acl\Acl',
            'BillingStrategies' => 'Tillikum\Billing\Event\Strategy\Strategies',
            'Di' => 'Zend\Di\Di',
            'EntityManager' => 'Doctrine\ORM\EntityManager',
            'Jobs' => 'Tillikum\Job\Jobs',
            'LoggedInMainNavigation' => 'Zend_Navigation',
            'LoggedOutMainNavigation' => 'Zend_Navigation',
            'Logger' => 'Zend\Log\Logger',
            'PendingBookings' => 'Tillikum\Booking\PendingBookings',
            'PersonTabNavigation' => 'Tillikum\Navigation\TabNavigation',
            'PersonForm' => 'Tillikum\Form\Person\Person',
            'PersonEntity' => 'Tillikum\Entity\Person\Person',
            'PersonSearchForm' => 'Tillikum\Form\Person\Search',
            'Reports' => 'Tillikum\Report\Reports',
            'RoleProvider' => 'Tillikum\Authorization\RoleProvider\Dummy',
            'SiteLogo' => 'Zend_Navigation_Page_Uri',
            'SyslogWriter' => 'Zend\Log\Writer\Syslog',
        ),
        'preferences' => array(
            'Doctrine\ORM\EntityManager' => 'EntityManager',
            'Zend\Authentication\Adapter\AdapterInterface' => 'Tillikum\Authentication\Adapter\Dummy',
            'Zend\Authentication\Storage\StorageInterface' => 'Zend\Authentication\Storage\Session',
            'Zend\Db\Adapter\AdapterInterface' => 'Zend\Db\Adapter\Adapter',
            'Zend\Db\Adapter\Profiler\ProfilerInterface' => 'Zend\Db\Adapter\Profiler\Profiler',
            'Zend\Db\Adapter\Driver\DriverInterface' => 'Zend\Db\Adapter\Driver\Pdo\Pdo',
            'Zend\Di\Di' => 'Di',
            'Zend\Session\SaveHandler\SaveHandlerInterface' => 'Zend\Session\SaveHandler\DbTableGateway',
        ),
        'BillingStrategies' => array(
            'parameters' => array(
                'array' => array(
                    1000 => 'Tillikum\Billing\Event\Strategy\Daily',
                    1001 => 'Tillikum\Billing\Event\Strategy\Nightly',
                    1002 => 'Tillikum\Billing\Event\Strategy\Weekly',
                    1003 => 'Tillikum\Billing\Event\Strategy\Monthly',
                    1004 => 'Tillikum\Billing\Event\Strategy\FixedRange',
                    1005 => 'Tillikum\Billing\Event\Strategy\Pass',
                ),
            ),
        ),
        'Jobs' => array(
            'parameters' => array(
                'array' => array(
                    1000 => 'Tillikum\Job\BillingEventProcessor',
                ),
            ),
        ),
        'LoggedInMainNavigation' => array(
            'parameters' => array(
                'pages' => array(
                    'billing' => array(
                        'module' => 'billing',
                        'title' => 'Billing section',
                        'label' => 'Billing',
                        'order' => 100,
                    ),
                    'booking' => array(
                        'module' => 'booking',
                        'title' => 'Booking section',
                        'label' => 'Bookings',
                        'order' => 200,
                    ),
                    'facility' => array(
                        'module' => 'facility',
                        'title' => 'Facilities section',
                        'label' => 'Facilities',
                        'order' => 300,
                    ),
                    'job' => array(
                        'module' => 'job',
                        'title' => 'Batch jobs section',
                        'label' => 'Jobs',
                        'order' => 400,
                    ),
                    'person' => array(
                        'module' => 'person',
                        'title' => 'People section',
                        'label' => 'People',
                        'order' => 500,
                    ),
                    'report' => array(
                        'module' => 'report',
                        'title' => 'Reporting section',
                        'label' => 'Reporting',
                        'order' => 600,
                    ),
                    'logout' => array(
                        'module' => 'default',
                        'controller' => 'auth',
                        'action' => 'logout',
                        'label' => 'Log out',
                        'order' => 700,
                    ),
                ),
            ),
        ),
        'LoggedOutMainNavigation' => array(
            'parameters' => array(
                'pages' => array(
                    'login' => array(
                        'module' => 'default',
                        'controller' => 'index',
                        'action' => 'index',
                        'label' => 'Log in',
                        'order' => 100,
                    ),
                ),
            ),
        ),
        'Logger' => array(
            'injections' => array(
                'addWriter' => array(
                    'writer' => 'SyslogWriter'
                ),
            ),
        ),
        'PersonTabNavigation' => array(
            'parameters' => array(
                'pages' => array(
                    'billing' => array(
                        'content_id' => 'person-billing',
                        'content_helper' => 'tabViewPersonBilling',
                        'label' => 'Billing',
                        'uri' => 'billing/summary/view/pid/%s',
                        'order' => 100,
                    ),
                    'contact' => array(
                        'content_id' => 'person-contact',
                        'content_helper' => 'tabViewPersonContact',
                        'label' => 'Contact',
                        'uri' => '#person-contact',
                        'order' => 200,
                    ),
                    'details' => array(
                        'content_id' => 'person-details',
                        'content_helper' => 'tabViewPersonDetails',
                        'label' => 'Details',
                        'uri' => '#person-details',
                        'order' => 300,
                    ),
                ),
                'options' => array(
                    'active' => 2,
                ),
            ),
        ),
        'SyslogWriter' => array(
            'parameters' => array(
                'params' => array(
                    'application' => 'tillikum',
                    'facility' => LOG_LOCAL0,
                ),
            ),
        ),
        'Zend\Db\TableGateway\TableGateway' => array(
            'parameters' => array(
                'table' => 'tillikum_session',
            ),
        ),
    ),
);

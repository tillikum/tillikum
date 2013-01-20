<?php

use Doctrine\ORM\EntityManager;
use Tillikum\Listener\ExtensionMetadata as ExtensionMetadataListener;
use Zend\Permissions;
use Zend\ServiceManager;

return array(
    'initializers' => array(
        'Acl' => function ($instance, $serviceManager) {
            if ($instance instanceof Permissions\Acl\Acl) {
                $authenticationService = $serviceManager->get(
                    'Zend\Authentication\AuthenticationService'
                );

                if (!$authenticationService->hasIdentity()) {
                    throw new ServiceManager\Exception\RuntimeException(
                        sprintf(
                            'The authentication service %s does not contain ' .
                            'a valid identity, so %s cannot be configured.',
                            get_class($authenticationService),
                            get_class($instance)
                        )
                    );
                }

                $identity = $authenticationService->getIdentity();

                $roleProvider = $serviceManager->get('Di')
                    ->get(
                        'RoleProvider',
                        array(
                            'identity' => $identity,
                        )
                    );

                $roleProvider->configureAcl($instance);
            }
        },
        'EntityManager' => function ($instance, $serviceManager) {
            if ($instance instanceof EntityManager) {
                $metadataListener = new ExtensionMetadataListener();

                $eventManager = $instance->getEventManager();

                $eventManager->addEventSubscriber($metadataListener);
            }
        },
    ),
);

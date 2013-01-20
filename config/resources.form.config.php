<?php

return array(
    'attribs' => array(
        'accept-charset' => 'UTF-8',
    ),
    'decorators' => array(
        100 => 'FormElements',
        110 => array(
            100 => 'HtmlTag',
            110 => array(
                'tag' => 'dl',
            ),
        ),
        120 => 'Form',
        130 => array(
            100 => 'Warnings',
            110 => array(
                'placement' => 'prepend',
            ),
        ),
    ),
    'disableLoadDefaultDecorators' => true,
    'prefixPath' => array(
        100 => array(
            'prefix' => 'Tillikum\Form\\',
            'path' => APPLICATION_PATH . '/library/Tillikum/Form',
        ),
        101 => array(
            'prefix' => 'Tillikum_Form',
            'path' => APPLICATION_PATH . '/library/Tillikum/Form',
        ),
    ),
);

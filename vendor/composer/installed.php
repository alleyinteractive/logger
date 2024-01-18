<?php return array(
    'root' => array(
        'name' => 'alleyinteractive/logger',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => NULL,
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'alleyinteractive/composer-wordpress-autoloader' => array(
            'pretty_version' => 'v1.0.0',
            'version' => '1.0.0.0',
            'reference' => '5f72de0cc7060c2475117c3231335b0899200230',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/../alleyinteractive/composer-wordpress-autoloader',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'alleyinteractive/logger' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => NULL,
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'alleyinteractive/wordpress-autoloader' => array(
            'pretty_version' => 'v1.1.1',
            'version' => '1.1.1.0',
            'reference' => 'c7599d95f49f1cdc38fad19944a50b19ec0dd6ca',
            'type' => 'library',
            'install_path' => __DIR__ . '/../alleyinteractive/wordpress-autoloader',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'monolog/monolog' => array(
            'pretty_version' => '2.9.2',
            'version' => '2.9.2.0',
            'reference' => '437cb3628f4cf6042cc10ae97fc2b8472e48ca1f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../monolog/monolog',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log' => array(
            'pretty_version' => '2.0.0',
            'version' => '2.0.0.0',
            'reference' => 'ef29f6d262798707a9edd554e2b82517ef3a9376',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/log',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log-implementation' => array(
            'dev_requirement' => false,
            'provided' => array(
                0 => '1.0.0 || 2.0.0 || 3.0.0',
            ),
        ),
    ),
);

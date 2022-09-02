<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit33a0153a67989e9b4d1ca6ec3724b562
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit33a0153a67989e9b4d1ca6ec3724b562', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit33a0153a67989e9b4d1ca6ec3724b562', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit33a0153a67989e9b4d1ca6ec3724b562::getInitializer($loader));

        $loader->setApcuPrefix('iRqmC4pDTAlanV5nWxDsB');
        $loader->register(true);

        $includeFiles = \Composer\Autoload\ComposerStaticInit33a0153a67989e9b4d1ca6ec3724b562::$files;
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequire33a0153a67989e9b4d1ca6ec3724b562($fileIdentifier, $file);
        }

        return $loader;
    }
}

/**
 * @param string $fileIdentifier
 * @param string $file
 * @return void
 */
function composerRequire33a0153a67989e9b4d1ca6ec3724b562($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;

        require $file;
    }
}

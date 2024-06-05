<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcdc540639fbf1cb4b940a9197d9773c3
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Valitron\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Valitron\\' => 
        array (
            0 => __DIR__ . '/..' . '/vlucas/valitron/src/Valitron',
        ),
    );

    public static $classMap = array (
        'AltoRouter' => __DIR__ . '/..' . '/altorouter/altorouter/AltoRouter.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitcdc540639fbf1cb4b940a9197d9773c3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitcdc540639fbf1cb4b940a9197d9773c3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitcdc540639fbf1cb4b940a9197d9773c3::$classMap;

        }, null, ClassLoader::class);
    }
}
<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit927b36c4d1331fb6bd47b55cf3119ecc
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stomp\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stomp\\' => 
        array (
            0 => __DIR__ . '/..' . '/stomp-php/stomp-php/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit927b36c4d1331fb6bd47b55cf3119ecc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit927b36c4d1331fb6bd47b55cf3119ecc::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit927b36c4d1331fb6bd47b55cf3119ecc::$classMap;

        }, null, ClassLoader::class);
    }
}

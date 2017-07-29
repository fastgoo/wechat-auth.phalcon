<?php

/**
 * Registering an autoloader
 */
$loader = new \Phalcon\Loader();

$loader->registerDirs(
    [
        $config->application->viewsDir
    ]
)->register();


/**
 * 注册命名空间
 */
$loader->registerNamespaces([
    'Services' => '../app/services',
])->register();

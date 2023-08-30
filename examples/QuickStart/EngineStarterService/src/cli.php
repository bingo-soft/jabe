<?php

use Phalcon\Autoload\Loader;

$loader = new Loader();
$loader->setNamespaces(
    [
       'EngineStarterService' => dirname('.')
    ]
);
$loader->setFiles([ "../vendor/autoload.php" ]);
$loader->register();

try {
    $application = new \EngineStarterService\Bootstrap();
    $application->run();
} catch (Exception $e) {
    echo $e->getMessage();
}

<?php

require '../vendor/autoload.php';

//Build process engine
$conf = \Jabe\ProcessEngineConfiguration::createProcessEngineConfigurationFromResource(realpath('./engine.cfg.xml'));
$engine = $conf->buildProcessEngine();

$repositoryService = $engine->getRepositoryService();

//Deploy process 1
$fd = fopen('./process3.bpmn', 'r');
$repositoryService->createDeployment()->addInputStream(realpath('./process3.bpmn'), $fd)->name('Process_3')->deploy();

try {
    fclose($fd);
} catch (\Throwable $t) {
    //close silently
}

//Start deployed process
$runtimeService = $engine->getRuntimeService();
$runtimeService->startProcessInstanceByKey('Process_3', ['var_1' => 1, 'var_2' => 2, 'var_3' => 3]);

while (true) {
    echo "Engine is running!\n";
    sleep(10);
}

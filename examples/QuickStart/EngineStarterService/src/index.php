<?php

require '../vendor/autoload.php';

$conf = \Jabe\ProcessEngineConfiguration::createProcessEngineConfigurationFromResource(realpath('./engine.cfg.xml'));
$engine = $conf->buildProcessEngine();

var_dump(get_class($engine));

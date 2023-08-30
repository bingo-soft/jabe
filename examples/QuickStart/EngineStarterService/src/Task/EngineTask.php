<?php

namespace EngineStarterService\Task;

use Phalcon\Cli\Task;
use EngineStarterService\Service\Engine;

class EngineTask extends Task
{
    public function startAction()
    {
        $server = new Engine(getenv('KAFKA_BROKERS', true), ['commands']);
        $server->start();
    }
}

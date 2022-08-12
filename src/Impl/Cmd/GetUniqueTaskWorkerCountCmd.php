<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUniqueTaskWorkerCountCmd implements CommandInterface, \Serializable
{
    protected $startTime;
    protected $endTime;

    public function __construct(string $startTime, string $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function serialize()
    {
        return json_encode([
            'startTime' => $this->startTime,
            'endTime' => $this->endTime
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->startTime = $json->startTime;
        $this->endTime = $json->endTime;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getMeterLogManager()->findUniqueTaskWorkerCount($this->startTime, $this->endTime);
    }
}

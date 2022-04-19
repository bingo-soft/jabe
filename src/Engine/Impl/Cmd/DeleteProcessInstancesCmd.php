<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class DeleteProcessInstancesCmd extends AbstractDeleteProcessInstanceCmd implements CommandInterface, \Serializable
{
    protected $processInstanceIds = [];

    public function __construct(
        array $processInstanceIds,
        string $deleteReason,
        bool $skipCustomListeners,
        bool $externallyTerminated,
        bool $skipSubprocesses,
        bool $failIfNotExists
    ) {
        $this->processInstanceIds = $processInstanceIds;
        $this->deleteReason = $deleteReason;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->externallyTerminated = $externallyTerminated;
        $this->skipSubprocesses = $skipSubprocesses;
        $this->failIfNotExists = $failIfNotExists;
    }

    public function serialize()
    {
        return json_encode([
            'processInstanceIds' => $this->processInstanceIds,
            'deleteReason' => $this->deleteReason,
            'skipCustomListeners' => $this->skipCustomListeners,
            'externallyTerminated' => $this->externallyTerminated,
            'skipSubprocesses' => $this->skipSubprocesses,
            'failIfNotExists' => $this->failIfNotExists
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processInstanceIds = $json->processInstanceIds;
        $this->deleteReason = $json->deleteReason;
        $this->skipCustomListeners = $json->skipCustomListeners;
        $this->externallyTerminated = $json->externallyTerminated;
        $this->skipSubprocesses = $json->skipSubprocesses;
        $this->failIfNotExists = $json->failIfNotExists;
    }

    public function execute(CommandContext $commandContext)
    {
        foreach ($this->processInstanceIds as $processInstanceId) {
            $this->deleteProcessInstance($commandContext, $this->processInstanceId, $this->deleteReason, $this->skipCustomListeners, $this->externallyTerminated, false, $this->skipSubprocesses);
        }
        return null;
    }
}

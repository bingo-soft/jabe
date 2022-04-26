<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class DeleteProcessInstanceCmd extends AbstractDeleteProcessInstanceCmd implements CommandInterface, \Serializable
{
    protected $processInstanceId;
    protected $skipIoMappings;
    protected $skipSubprocesses;

    public function __construct(
        string $processInstanceId,
        string $deleteReason,
        bool $skipCustomListeners,
        bool $externallyTerminated,
        bool $skipIoMappings,
        bool $skipSubprocesses,
        bool $failIfNotExists
    ) {
        $this->processInstanceId = $processInstanceId;
        $this->deleteReason = $deleteReason;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->externallyTerminated = $externallyTerminated;
        $this->skipIoMappings = $skipIoMappings;
        $this->skipSubprocesses = $skipSubprocesses;
        $this->failIfNotExists = $failIfNotExists;
    }

    public function serialize()
    {
        return json_encode([
            'processInstanceId' => $this->processInstanceId,
            'deleteReason' => $this->deleteReason,
            'skipCustomListeners' => $this->skipCustomListeners,
            'externallyTerminated' => $this->externallyTerminated,
            'skipIoMappings' => $this->skipIoMappings,
            'skipSubprocesses' => $this->skipSubprocesses,
            'failIfNotExists' => $this->failIfNotExists
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processInstanceId = $json->processInstanceId;
        $this->deleteReason = $json->deleteReason;
        $this->skipCustomListeners = $json->skipCustomListeners;
        $this->externallyTerminated = $json->externallyTerminated;
        $this->skipIoMappings = $json->skipIoMappings;
        $this->skipIoMappings = $json->skipIoMappings;
    }

    public function execute(CommandContext $commandContext)
    {
        $this->deleteProcessInstance($commandContext, $this->processInstanceId, $this->deleteReason, $this->skipCustomListeners, $this->externallyTerminated, $this->skipIoMappings, $this->skipSubprocesses);
        return null;
    }
}

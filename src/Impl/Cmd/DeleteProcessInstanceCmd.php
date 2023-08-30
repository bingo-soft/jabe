<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class DeleteProcessInstanceCmd extends AbstractDeleteProcessInstanceCmd implements CommandInterface
{
    protected $processInstanceId;
    protected bool $skipIoMappings = false;
    protected bool $skipSubprocesses = false;

    public function __construct(
        ?string $processInstanceId,
        ?string $deleteReason,
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

    public function __serialize(): array
    {
        return [
            'processInstanceId' => $this->processInstanceId,
            'deleteReason' => $this->deleteReason,
            'skipCustomListeners' => $this->skipCustomListeners,
            'externallyTerminated' => $this->externallyTerminated,
            'skipIoMappings' => $this->skipIoMappings,
            'skipSubprocesses' => $this->skipSubprocesses,
            'failIfNotExists' => $this->failIfNotExists
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processInstanceId = $data['processInstanceId'];
        $this->deleteReason = $data['deleteReason'];
        $this->skipCustomListeners = $data['skipCustomListeners'];
        $this->externallyTerminated = $data['externallyTerminated'];
        $this->skipIoMappings = $data['skipIoMappings'];
        $this->skipIoMappings = $data['skipIoMappings'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $this->deleteProcessInstance($commandContext, $this->processInstanceId, $this->deleteReason, $this->skipCustomListeners, $this->externallyTerminated, $this->skipIoMappings, $this->skipSubprocesses);
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}

<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class DeleteProcessInstancesCmd extends AbstractDeleteProcessInstanceCmd implements CommandInterface
{
    protected $processInstanceIds = [];

    public function __construct(
        array $processInstanceIds,
        ?string $deleteReason,
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

    public function __serialize(): array
    {
        return [
            'processInstanceIds' => $this->processInstanceIds,
            'deleteReason' => $this->deleteReason,
            'skipCustomListeners' => $this->skipCustomListeners,
            'externallyTerminated' => $this->externallyTerminated,
            'skipSubprocesses' => $this->skipSubprocesses,
            'failIfNotExists' => $this->failIfNotExists
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processInstanceIds = $data['processInstanceIds'];
        $this->deleteReason = $data['deleteReason'];
        $this->skipCustomListeners = $data['skipCustomListeners'];
        $this->externallyTerminated = $data['externallyTerminated'];
        $this->skipSubprocesses = $data['skipSubprocesses'];
        $this->failIfNotExists = $data['failIfNotExists'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        foreach ($this->processInstanceIds as $processInstanceId) {
            $this->deleteProcessInstance($commandContext, $this->processInstanceId, $this->deleteReason, $this->skipCustomListeners, $this->externallyTerminated, false, $this->skipSubprocesses);
        }
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}

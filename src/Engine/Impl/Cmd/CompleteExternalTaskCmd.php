<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Persistence\Entity\ExternalTaskEntity;

class CompleteExternalTaskCmd extends HandleExternalTaskCmd
{
    protected $variables = [];
    protected $localVariables = [];

    public function __construct(string $externalTaskId, string $workerId, array $variables, array $localVariables)
    {
        parent::__construct($externalTaskId, $workerId);
        $this->localVariables = $localVariables;
        $this->variables = $variables;
    }

    public function getErrorMessageOnWrongWorkerAccess(): string
    {
        return "External Task " . $this->externalTaskId . " cannot be completed by worker '" . $this->workerId;
    }

    public function executeTask(ExternalTaskEntity $externalTask)
    {
        $this->externalTask->complete($this->variables, $this->localVariables);
    }
}

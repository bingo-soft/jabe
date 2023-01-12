<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Persistence\Entity\ExternalTaskEntity;
use Jabe\Impl\Util\EnsureUtil;

class HandleExternalTaskBpmnErrorCmd extends HandleExternalTaskCmd
{
    /**
     * The error code of the corresponding bpmn error.
     */
    protected $errorCode;
    protected $errorMessage;
    protected $variables = [];

    public function __construct(?string $externalTaskId, ?string $workerId, ?string $errorCode, ?string $errorMessage = null, array $variables = [])
    {
        parent::__construct($externalTaskId, $workerId);
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->variables = $variables;
    }

    protected function validateInput(): void
    {
        parent::validateInput();
        EnsureUtil::ensureNotNull("errorCode", "errorCode", $this->errorCode);
    }

    public function getErrorMessageOnWrongWorkerAccess(): ?string
    {
        return "Bpmn error of External Task " . $this->externalTaskId . " cannot be reported by worker '" . $this->workerId;
    }

    public function executeTask(ExternalTaskEntity $externalTask)
    {
        $externalTask->bpmnError($this->errorCode, $this->errorMessage, $this->variables);
    }
}

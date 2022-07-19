<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Persistence\Entity\ExternalTaskEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;

class HandleExternalTaskFailureCmd extends HandleExternalTaskCmd
{
    protected $errorMessage;
    protected $errorDetails;
    protected $retryDuration;
    protected $retries;
    protected $variables = [];
    protected $localVariables = [];

    /**
     * Overloaded constructor to support short and full error messages
     *
     * @param externalTaskId
     * @param workerId
     * @param errorMessage
     * @param errorDetails
     * @param retries
     * @param retryDuration
     */
    public function __construct(
        string $externalTaskId,
        string $workerId,
        string $errorMessage,
        string $errorDetails,
        int $retries,
        int $retryDuration,
        array $variables,
        array $localVariables
    ) {
        parent::__construct($externalTaskId, $workerId);
        $this->errorMessage = $errorMessage;
        $this->errorDetails = $errorDetails;
        $this->retries = $retries;
        $this->retryDuration = $retryDuration;
        $this->variables = $variables;
        $this->localVariables = $localVariables;
    }

    public function executeTask(ExternalTaskEntity $externalTask)
    {
        $externalTask->failed($this->errorMessage, $this->errorDetails, $this->retries, $this->retryDuration, $this->variables, $this->localVariables);
    }

    protected function validateInput(): void
    {
        parent::validateInput();
        EnsureUtil::ensureGreaterThanOrEqual("The number of retries cannot be negative", "retries", $this->retries, 0);
        EnsureUtil::ensureGreaterThanOrEqual("Retry duration cannot be negative", "retryDuration", $this->retryDuration, 0);
    }

    public function getErrorMessageOnWrongWorkerAccess(): string
    {
        return "Failure of External Task " . $this->externalTaskId . " cannot be reported by worker '" . $this->workerId;
    }
}

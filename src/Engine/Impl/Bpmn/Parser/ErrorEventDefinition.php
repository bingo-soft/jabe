<?php

namespace Jabe\Engine\Impl\Bpmn\Parser;

use Jabe\Engine\ProcessEngineException;

class ErrorEventDefinition implements \Serializable
{
    protected $handlerActivityId;
    protected $errorCode;
    protected $precedence = 0;
    protected $errorCodeVariable;
    protected $errorMessageVariable;

    public function __construct(string $handlerActivityId)
    {
        $this->handlerActivityId = $handlerActivityId;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function setErrorCode(string $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    public function getHandlerActivityId(): string
    {
        return $this->handlerActivityId;
    }

    public function getPrecedence(): int
    {
        // handlers with error code take precedence over catchall-handlers
        return $this->precedence + ($this->errorCode !== null ? 1 : 0);
    }

    public function setPrecedence(int $precedence): void
    {
        $this->precedence = $precedence;
    }

    public function catchesError(string $errorCode): bool
    {
        return $this->errorCode === null || $this->errorCode == $errorCode;
    }

    public function catchesException(\Exception $ex): bool
    {

        if ($this->errorCode === null) {
            return false;
        } else {
            // unbox exception
            while ($ex instanceof ProcessEngineException && $ex->getCause() !== null) {
                $ex = $ex->getCause();
            }

            // check exception hierarchy
            $exceptionClass = get_class($ex);
            do {
                if ($this->errorCode == $exceptionClass) {
                    return true;
                }
                $exceptionClass = get_parent_class($exceptionClass);
            } while ($exceptionClass);

            return false;
        }
    }

    public function setErrorCodeVariable(string $errorCodeVariable): void
    {
        $this->errorCodeVariable = $errorCodeVariable;
    }

    public function getErrorCodeVariable(): string
    {
        return $this->errorCodeVariable;
    }

    public function setErrorMessageVariable(string $errorMessageVariable): void
    {
        $this->errorMessageVariable = $errorMessageVariable;
    }

    public function getErrorMessageVariable(): string
    {
        return $this->errorMessageVariable;
    }
}

<?php

namespace Jabe\Impl\Bpmn\Parser;

use Jabe\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;

class Error
{
    protected $id;
    protected $errorCode;
    private $errorMessageExpression;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setErrorCode(?string $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    public function getErrorMessageExpression(): ?ParameterValueProviderInterface
    {
        return $this->errorMessageExpression;
    }

    public function setErrorMessageExpression(ParameterValueProviderInterface $errorMessageExpression): void
    {
        $this->errorMessageExpression = $errorMessageExpression;
    }

    public function getMessage(): ?string
    {
        if ($this->errorMessageExpression !== null) {
            return $this->errorMessageExpression->getValue();
        }
        return null;
    }
}

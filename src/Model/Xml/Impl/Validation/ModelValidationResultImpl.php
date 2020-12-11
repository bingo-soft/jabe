<?php

namespace BpmPlatform\Model\Xml\Impl\Validation;

use BpmPlatform\Model\Xml\Validation\{
    ValidationResultInterface,
    ValidationResultType
};
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

class ModelValidationResultImpl implements ValidationResultInterface
{
    protected $code;
    protected $type;
    protected $element;
    protected $message;

    public function __construct(
        ModelElementInstanceInterface $element,
        string $type,
        int $code,
        string $message
    ) {
        $this->element = $element;
        $this->type = $type;
        $this->code = $code;
        $this->message = $message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getElement(): ModelElementInstanceInterface
    {
        return $this->element;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }
}

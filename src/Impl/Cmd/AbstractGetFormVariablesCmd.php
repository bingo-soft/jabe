<?php

namespace Jabe\Impl\Cmd;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Form\FormFieldInterface;
use Jabe\Impl\Interceptor\CommandInterface;
use Jabe\Variable\Value\TypedValueInterface;

abstract class AbstractGetFormVariablesCmd implements CommandInterface
{
    public $resourceId;
    public $formVariableNames;
    protected $deserializeObjectValues;

    public function __construct(?string $resourceId, array $formVariableNames, bool $deserializeObjectValues)
    {
        $this->resourceId = $resourceId;
        $this->formVariableNames = $formVariableNames;
        $this->deserializeObjectValues = $deserializeObjectValues;
    }

    public function __serialize(): array
    {
        return [
            'resourceId' => $this->resourceId,
            'formVariableNames' => $this->formVariableNames,
            'deserializeObjectValues' => $this->deserializeObjectValues
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->resourceId = $data['resourceId'];
        $this->formVariableNames = $data['formVariableNames'];
        $this->deserializeObjectValues = $data['deserializeObjectValues'];
    }

    protected function createVariable(FormFieldInterface $formField, VariableScopeInterface $variableScope): ?TypedValueInterface
    {
        $value = $formField->getValue();
        return $value;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}

<?php

namespace Jabe\Impl\Cmd;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Form\FormFieldInterface;
use Jabe\Impl\Interceptor\CommandInterface;
use Jabe\Variable\Value\TypedValueInterface;

abstract class AbstractGetFormVariablesCmd implements CommandInterface, \Serializable
{
    public $resourceId;
    public $formVariableNames;
    protected $deserializeObjectValues;

    public function __construct(string $resourceId, array $formVariableNames, bool $deserializeObjectValues)
    {
        $this->resourceId = $resourceId;
        $this->formVariableNames = $formVariableNames;
        $this->deserializeObjectValues = $deserializeObjectValues;
    }

    public function serialize()
    {
        return json_encode([
            'resourceId' => $this->resourceId,
            'formVariableNames' => $this->formVariableNames,
            'deserializeObjectValues' => $this->deserializeObjectValues
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->resourceId = $json->resourceId;
        $this->formVariableNames = $json->formVariableNames;
        $this->deserializeObjectValues = $json->deserializeObjectValues;
    }

    protected function createVariable(FormFieldInterface $formField, VariableScopeInterface $variableScope): ?TypedValueInterface
    {
        $value = $formField->getValue();
        return $value;
    }
}

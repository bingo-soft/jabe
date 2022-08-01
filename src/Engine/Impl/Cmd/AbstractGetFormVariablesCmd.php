<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Form\FormFieldInterface;
use Jabe\Engine\Impl\Interceptor\CommandInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

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

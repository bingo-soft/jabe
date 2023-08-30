<?php

namespace Jabe\Impl\Core\Model;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Variable\{
    VariableMapInterface,
    Variables
};

class CallableElement extends BaseCallableElement
{
    protected $businessKeyValueProvider;
    protected $inputs;
    protected $outputs;
    protected $outputsLocal;

    public function __construct()
    {
        $this->inputs = [];
        $this->outputs = [];
        $this->outputsLocal = [];
    }

    public function getBusinessKey(VariableScopeInterface $variableScope): ?string
    {
        if ($this->businessKeyValueProvider === null) {
            return null;
        }

        $result = $this->businessKeyValueProvider->getValue($variableScope);

        if ($result !== null && !is_string($result)) {
            throw new \Exception("Cannot cast '" . $result . "' to string");
        }

        return strval($result);
    }

    public function getBusinessKeyValueProvider(): ?ParameterValueProviderInterface
    {
        return $this->businessKeyValueProvider;
    }

    public function setBusinessKeyValueProvider(ParameterValueProviderInterface $businessKeyValueProvider): void
    {
        $this->businessKeyValueProvider = $businessKeyValueProvider;
    }

    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function addInput(CallableElementParameter $input): void
    {
        $this->inputs[] = $input;
    }

    public function addInputs(array $inputs): void
    {
        $this->inputs = array_merge($this->inputs, $inputs);
    }

    public function getInputVariables(VariableScopeInterface $variableScope): VariableMapInterface
    {
        $inputs = $this->getInputs();
        return $this->getVariables($inputs, $variableScope);
    }

    public function getOutputs(): array
    {
        return $this->outputs;
    }

    public function getOutputsLocal(): array
    {
        return $this->outputsLocal;
    }

    public function addOutput(CallableElementParameter $output): void
    {
        $this->outputs[] = $output;
    }

    public function addOutputLocal(CallableElementParameter $output): void
    {
        $this->outputsLocal[] = $output;
    }

    public function addOutputs(array $outputs): void
    {
        $this->outputs = array_merge($this->outputs, $outputs);
    }

    public function getOutputVariables(VariableScopeInterface $calledElementScope): VariableMapInterface
    {
        $outputs = $this->getOutputs();
        return $this->getVariables($outputs, $calledElementScope);
    }

    public function getOutputVariablesLocal(VariableScopeInterface $calledElementScope): VariableMapInterface
    {
        $outputs = $this->getOutputsLocal();
        return $this->getVariables($outputs, $calledElementScope);
    }

    protected function getVariables(array $params, VariableScopeInterface $variableScope): VariableMapInterface
    {
        $result = Variables::createVariables();

        foreach ($params as $param) {
            $param->applyTo($variableScope, $result);
        }

        return $result;
    }
}

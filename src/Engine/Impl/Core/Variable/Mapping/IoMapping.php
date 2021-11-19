<?php

namespace BpmPlatform\Engine\Impl\Core\Variable\Mapping;

use BpmPlatform\Engine\Impl\Core\Variable\Scope\AbstractVariableScope;

class IoMapping
{
    protected $inputParameters = [];

    protected $outputParameters = [];

    public function executeInputParameters(AbstractVariableScope $variableScope): void
    {
        foreach ($this->getInputParameters() as $inputParameter) {
            $inputParameter->execute($variableScope);
        }
    }

    public function executeOutputParameters(AbstractVariableScope $variableScope): void
    {
        foreach ($this->getOutputParameters() as $outputParameter) {
            $outputParameter->execute($variableScope);
        }
    }

    public function addInputParameter(InputParameter $param): void
    {
        $this->inputParameters[] = $param;
    }

    public function addOutputParameter(OutputParameter $param): void
    {
        $this->outputParameters[] = $param;
    }

    public function getInputParameters(): array
    {
        return $this->inputParameters;
    }

    public function setInputParameters(array $inputParameters): void
    {
        $this->inputParameters = $inputParameters;
    }

    public function getOutputParameters(): array
    {
        return $this->outputParameters;
    }

    public function setOuputParameters(array $outputParameters): void
    {
        $this->outputParameters = $outputParameters;
    }
}

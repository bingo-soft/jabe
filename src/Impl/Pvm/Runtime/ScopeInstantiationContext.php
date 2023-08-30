<?php

namespace Jabe\Impl\Pvm\Runtime;

use Jabe\Impl\Core\Instance\CoreExecution;

class ScopeInstantiationContext
{
    protected $instantiationStack;
    protected $variables = [];
    protected $variablesLocal = [];

    public function applyVariables(CoreExecution $execution): void
    {
        $execution->setVariables($this->variables);
        $execution->setVariablesLocal($this->variablesLocal);
    }

    public function getInstantiationStack(): ?InstantiationStack
    {
        return $this->instantiationStack;
    }

    public function setInstantiationStack(InstantiationStack $instantiationStack): void
    {
        $this->instantiationStack = $instantiationStack;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    public function setVariablesLocal(array $variablesLocal): void
    {
        $this->variablesLocal = $variablesLocal;
    }
}

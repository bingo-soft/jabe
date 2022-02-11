<?php

namespace BpmPlatform\Engine\Impl\Form\Handler;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;
use BpmPlatform\Engine\Impl\Delegate\DelegateInvocation;
use BpmPlatform\Engine\Variable\VariableMapInterface;

class SubmitFormVariablesInvocation extends DelegateInvocation
{
    protected $formHandler;
    protected $properties;
    protected $variableScope;

    public function __construct(FormHandlerInterface $formHandler, VariableMapInterface $properties, VariableScopeInterface $variableScope)
    {
        parent::__construct(null, null);
        $this->formHandler = $formHandler;
        $this->properties = $properties;
        $this->variableScope = $variableScope;
    }

    protected function invoke(): void
    {
        $this->formHandler->submitFormVariables($this->properties, $this->variableScope);
    }
}

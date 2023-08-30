<?php

namespace Jabe\Impl\Form\Handler;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Delegate\DelegateInvocation;
use Jabe\Variable\VariableMapInterface;

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

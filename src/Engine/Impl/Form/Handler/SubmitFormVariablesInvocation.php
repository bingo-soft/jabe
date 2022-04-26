<?php

namespace Jabe\Engine\Impl\Form\Handler;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Delegate\DelegateInvocation;
use Jabe\Engine\Variable\VariableMapInterface;

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

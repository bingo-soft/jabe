<?php

namespace Jabe\Engine\Impl\Form\Handler;

use Jabe\Engine\Impl\Delegate\DelegateInvocation;
use Jabe\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;

class CreateStartFormInvocation extends DelegateInvocation
{
    protected $startFormHandler;
    protected $definition;

    public function __construct(StartFormHandlerInterface $startFormHandler, ProcessDefinitionEntity $definition)
    {
        parent::__construct(null, $definition);
        $this->startFormHandler = $startFormHandler;
        $this->definition = $definition;
    }

    protected function invoke()
    {
        $this->invocationResult = $this->startFormHandler->createStartFormData($this->definition);
    }
}

<?php

namespace BpmPlatform\Engine\Impl\Form;

use BpmPlatform\Engine\Form\StartFormDataInterface;
use BpmPlatform\Engine\Repository\ProcessDefinitionInterface;

class StartFormDataImpl extends FormDataImpl implements StartFormDataInterface
{
    protected $processDefinition;

    // getters and setters //////////////////////////////////////////////////////

    public function getProcessDefinition(): ProcessDefinitionInterface
    {
        return $this->processDefinition;
    }

    public function setProcessDefinition(ProcessDefinitionInterface $processDefinition): void
    {
        $this->processDefinition = $processDefinition;
    }
}

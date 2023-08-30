<?php

namespace Jabe\Impl\Form;

use Jabe\Form\StartFormDataInterface;
use Jabe\Repository\ProcessDefinitionInterface;

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

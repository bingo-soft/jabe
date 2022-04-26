<?php

namespace Jabe\Engine\Impl\Form;

use Jabe\Engine\Form\StartFormDataInterface;
use Jabe\Engine\Repository\ProcessDefinitionInterface;

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

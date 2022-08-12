<?php

namespace Jabe\Impl\Form\Handler;

use Jabe\Form\StartFormDataInterface;
use Jabe\Impl\Persistence\Entity\ProcessDefinitionEntity;

interface StartFormHandlerInterface extends FormHandlerInterface
{
    public function createStartFormData(ProcessDefinitionEntity $processDefinition): StartFormDataInterface;
}

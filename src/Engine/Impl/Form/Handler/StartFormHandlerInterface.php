<?php

namespace BpmPlatform\Engine\Impl\Form\Handler;

use BpmPlatform\Engine\Form\StartFormDataInterface;
use BpmPlatform\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;

interface StartFormHandlerInterface extends FormHandlerInterface
{
    public function createStartFormData(ProcessDefinitionEntity $processDefinition): StartFormDataInterface;
}

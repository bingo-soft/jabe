<?php

namespace Jabe\Engine\Impl\Form\Handler;

use Jabe\Engine\Form\StartFormDataInterface;
use Jabe\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;

interface StartFormHandlerInterface extends FormHandlerInterface
{
    public function createStartFormData(ProcessDefinitionEntity $processDefinition): StartFormDataInterface;
}

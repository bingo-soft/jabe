<?php

namespace BpmPlatform\Engine\Form;

use BpmPlatform\Engine\Repository\ProcessDefinitionInterface;

interface StartFormDataInterface
{
    public function getProcessDefinition(): ProcessDefinitionInterface;
}

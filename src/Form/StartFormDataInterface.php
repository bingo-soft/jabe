<?php

namespace Jabe\Form;

use Jabe\Repository\ProcessDefinitionInterface;

interface StartFormDataInterface
{
    public function getProcessDefinition(): ProcessDefinitionInterface;
}

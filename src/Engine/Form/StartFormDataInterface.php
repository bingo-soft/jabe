<?php

namespace Jabe\Engine\Form;

use Jabe\Engine\Repository\ProcessDefinitionInterface;

interface StartFormDataInterface
{
    public function getProcessDefinition(): ProcessDefinitionInterface;
}

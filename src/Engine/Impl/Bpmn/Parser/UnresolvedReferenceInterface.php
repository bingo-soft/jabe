<?php

namespace Jabe\Engine\Impl\Bpmn\Parser;

use Jabe\Engine\Impl\Pvm\Process\ProcessDefinitionImpl;

interface UnresolvedReferenceInterface
{
    public function resolve(ProcessDefinitionImpl $processDefinition): void;
}

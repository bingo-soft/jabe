<?php

namespace Jabe\Impl\Bpmn\Parser;

use Jabe\Impl\Pvm\Process\ProcessDefinitionImpl;

interface UnresolvedReferenceInterface
{
    public function resolve(ProcessDefinitionImpl $processDefinition): void;
}

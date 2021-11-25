<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Parser;

use BpmPlatform\Engine\Impl\Pvm\Process\ProcessDefinitionImpl;

interface UnresolvedReferenceInterface
{
    public function resolve(ProcessDefinitionImpl $processDefinition): void;
}

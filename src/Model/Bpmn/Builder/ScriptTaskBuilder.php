<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ScriptTaskInterface;

class ScriptTaskBuilder extends AbstractScriptTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ScriptTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, ScriptTaskBuilder::class);
    }
}

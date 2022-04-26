<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ScriptTaskInterface;

class ScriptTaskBuilder extends AbstractScriptTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ScriptTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, ScriptTaskBuilder::class);
    }
}

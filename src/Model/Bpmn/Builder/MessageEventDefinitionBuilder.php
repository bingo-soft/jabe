<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\MessageEventDefinitionInterface;

class MessageEventDefinitionBuilder extends AbstractMessageEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        MessageEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, MessageEventDefinitionBuilder::class);
    }
}

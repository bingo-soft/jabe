<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\MessageEventDefinitionInterface;

class MessageEventDefinitionBuilder extends AbstractMessageEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        MessageEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, MessageEventDefinitionBuilder::class);
    }
}

<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    UserTaskInterface
};

class UserTaskBuilder extends AbstractUserTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        UserTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, UserTaskBuilder::class);
    }
}

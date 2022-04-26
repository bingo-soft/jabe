<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
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

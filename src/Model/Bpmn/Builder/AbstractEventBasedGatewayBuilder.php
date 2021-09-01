<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\EventBasedGatewayInterface;

abstract class AbstractEventBasedGatewayBuilder extends AbstractGatewayBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EventBasedGatewayInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function instantiate(): AbstractEventBasedGatewayBuilder
    {
        $this->element->setInstantiate(true);
        return $this;
    }

    public function eventGatewayType(string $eventGatewayType): AbstractEventBasedGatewayBuilder
    {
        $this->element->setEventGatewayType($eventGatewayType);
        return $this;
    }

    public function asyncAfter(bool $isCamundaAsyncAfter): AbstractEventBasedGatewayBuilder
    {
        throw new \Exception("'asyncAfter' is not supported for 'Event Based Gateway'");
    }
}

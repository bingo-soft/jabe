<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\RootElementInterface;

abstract class AbstractRootElementBuilder extends AbstractBaseElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        RootElementInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }
}

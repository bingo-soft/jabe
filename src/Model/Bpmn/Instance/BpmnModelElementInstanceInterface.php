<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\AbstractBaseElementBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface BpmnModelElementInstanceInterface extends ModelElementInstanceInterface
{
    public function builder(): AbstractBaseElementBuilder;

    public function isScope(): bool;

    public function getScope(): ?BpmnModelElementInstanceInterface;
}

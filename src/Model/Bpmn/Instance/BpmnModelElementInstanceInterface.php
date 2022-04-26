<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\AbstractBaseElementBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

interface BpmnModelElementInstanceInterface extends ModelElementInstanceInterface
{
    public function builder(): AbstractBaseElementBuilder;

    public function isScope(): bool;

    public function getScope(): ?BpmnModelElementInstanceInterface;
}

<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface DataObjectReference extends FlowElementInterface, ItemAwareElementInterface
{
    public function getDataObject(): DataObjectInterface;

    public function setDataObject(DataObjectInterface $dataObject): void;
}

<?php

namespace Jabe\Model\Bpmn\Instance;

interface DataStoreReferenceInterface extends FlowElementInterface, ItemAwareElementInterface
{
    public function getDataStore(): DataStoreInterface;

    public function setDataStore(DataStoreInterface $dataStore): void;
}

<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ItemAwareElementInterface extends BaseElementInterface
{
    public function getItemSubject(): ItemDefinitionInterface;

    public function setItemSubject(ItemDefinitionInterface $itemSubject): void;

    public function getDataState(): DataStateInterface;

    public function setDataState(DataStateInterface $dataState): void;
}

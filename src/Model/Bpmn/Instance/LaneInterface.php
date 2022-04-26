<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Impl\Instance\{
    ChildLaneSet,
    PartitionElement
};

interface LaneInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getPartitionElement(): PartitionElement;

    public function setPartitionElement(PartitionElement $partitionElement): void;

    public function getPartitionElementChild(): PartitionElement;

    public function setPartitionElementChild(PartitionElement $partitionElement): void;

    public function getFlowNodeRefs(): array;

    public function getChildLaneSet(): ChildLaneSet;

    public function setChildLaneSet(ChildLaneSet $childLaneSet): void;
}

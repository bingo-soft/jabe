<?php

namespace BpmPlatform\Model\Xml\Type\Child;

use BpmPlatform\Model\Xml\ModelInterface;
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ChildElementCollectionInterface
{
    public function isImmutable(): bool;

    public function getMinOccurs(): int;

    public function getMaxOccurs(): int;

    public function getChildElementType(ModelInterface $model): ModelElementTypeInterface;

    public function getChildElementTypeClass(): string;

    public function getParentElementType(): ModelElementTypeInterface;

    public function get(ModelElementInstanceInterface $modelElement): array;
}

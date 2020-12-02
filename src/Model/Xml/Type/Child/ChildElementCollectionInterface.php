<?php

namespace BpmPlatform\Model\Xml\Type\Child;

use BpmPlatform\Model\Xml\ModelInterface;
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ChildElementCollectionInterface extends ModelElementInstanceInterface
{
    public function isImmutable(): bool;

    public function getMinOccurs(): int;

    public function getMaxOccurs(): int;

    public function getChildElementType(ModelInterface $model): ModelElementTypeInterface;

    /**
     * @return mixed
     */
    public function getChildElementTypeClass();

    public function getParentElementType(): ModelElementTypeInterface;

    public function get(ModelElementInstanceInterface $element): array;
}

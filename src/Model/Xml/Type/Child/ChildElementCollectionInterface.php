<?php

namespace Jabe\Model\Xml\Type\Child;

use Jabe\Model\Xml\ModelInterface;
use Jabe\Model\Xml\Type\ModelElementTypeInterface;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

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

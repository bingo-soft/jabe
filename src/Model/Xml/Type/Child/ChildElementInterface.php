<?php

namespace BpmPlatform\Model\Xml\Type\Child;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ChildElementInterface extends ChildElementCollectionInterface
{
    public function setChild(
        ModelElementInstanceInterface $element,
        ModelElementInstanceInterface $newChildElement
    ): void;

    public function getChild(ModelElementInstanceInterface $element): ?ModelElementInstanceInterface;

    public function removeChild(ModelElementInstanceInterface $element): bool;
}

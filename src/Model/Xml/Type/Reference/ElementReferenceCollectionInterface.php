<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\Type\Child\ChildElementCollectionInterface;

interface ElementReferenceCollectionInterface extends ReferenceInterface
{
    public function getReferenceSourceCollection(): ChildElementCollectionInterface;

    public function getReferenceTargetElements(ModelElementInstanceImpl $referenceSourceElement): array;

    public function get(ModelElementInstanceInterface $modelElement): array;
}

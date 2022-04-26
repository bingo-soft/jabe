<?php

namespace Jabe\Model\Xml\Type\Reference;

use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Type\Child\ChildElementCollectionInterface;

interface ElementReferenceCollectionInterface extends ReferenceInterface
{
    public function getReferenceSourceCollection(): ChildElementCollectionInterface;

    public function getReferenceTargetElements(ModelElementInstanceImpl $referenceSourceElement): array;
}

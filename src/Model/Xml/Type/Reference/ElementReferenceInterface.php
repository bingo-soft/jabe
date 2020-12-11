<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ElementReferenceInterface extends ElementReferenceCollectionInterface
{
    public function getReferenceSource(
        ModelElementInstanceInterface $referenceSourceParent
    ): ?ModelElementInstanceInterface;

    public function getReferenceTargetElement(
        ModelElementInstanceImpl $referenceSourceParentElement
    ): ?ModelElementInstanceInterface;

    public function setReferenceTargetElement(
        ModelElementInstanceImpl $referenceSourceParentElement,
        ModelElementInstanceInterface $referenceTargetElement
    ): void;

    public function clearReferenceTargetElement(ModelElementInstanceImpl $referenceSourceParentElement): void;
}

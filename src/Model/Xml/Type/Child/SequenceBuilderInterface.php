<?php

namespace BpmPlatform\Model\Xml\Type\Child;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface SequenceBuilderInterface
{
    /**
     * @param mixed $childElementType
     */
    public function element($childElementType): ChildElementBuilderInterface;

    /**
     * @param mixed $childElementType
     */
    public function elementCollection($childElementType): ChildElementCollectionBuilderInterface;
}

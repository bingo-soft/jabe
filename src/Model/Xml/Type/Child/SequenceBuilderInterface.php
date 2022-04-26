<?php

namespace Jabe\Model\Xml\Type\Child;

use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

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

<?php

namespace BpmPlatform\Model\Xml\Type\Child;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\Reference\ElementReferenceCollectionBuilderInterface;

interface ChildElementCollectionBuilderInterface
{
    public function immutable(): ChildElementCollectionBuilderInterface;

    public function required(): ChildElementCollectionBuilderInterface;

    public function minOccurs(int $i): ChildElementCollectionBuilderInterface;

    public function maxOccurs(int $i): ChildElementCollectionBuilderInterface;

    public function build(): ChildElementCollectionInterface;

    /**
     * @param mixed $referenceTargetType
     */
    public function qNameElementReference($referenceTargetType): ElementReferenceCollectionBuilderInterface;

    /**
     * @param mixed $referenceTargetType
     */
    public function idElementReference($referenceTargetType): ElementReferenceCollectionBuilderInterface;

    /**
     * @param mixed $referenceTargetType
     */
    public function idsElementReference($referenceTargetType): ElementReferenceCollectionBuilderInterface;

    /**
     * @param mixed $referenceTargetType
     */
    public function uriElementReference($referenceTargetType): ElementReferenceCollectionBuilderInterface;
}

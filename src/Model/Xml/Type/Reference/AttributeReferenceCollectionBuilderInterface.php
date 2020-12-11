<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

interface AttributeReferenceCollectionBuilderInterface extends AttributeReferenceBuilderInterface
{
    public function build(): AttributeReferenceCollection;
}

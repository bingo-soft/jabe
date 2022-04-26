<?php

namespace Jabe\Model\Xml\Type\Reference;

interface AttributeReferenceCollectionBuilderInterface extends AttributeReferenceBuilderInterface
{
    public function build(): AttributeReferenceCollection;
}

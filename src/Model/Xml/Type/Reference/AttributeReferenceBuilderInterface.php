<?php

namespace Jabe\Model\Xml\Type\Reference;

interface AttributeReferenceBuilderInterface extends ReferenceBuilderInterface
{
    public function build(): AttributeReferenceInterface;
}

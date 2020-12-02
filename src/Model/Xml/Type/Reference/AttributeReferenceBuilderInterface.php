<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

interface AttributeReferenceBuilderInterface extends ReferenceBuilderInterface
{
    public function build(): AttributeReferenceInterface;
}

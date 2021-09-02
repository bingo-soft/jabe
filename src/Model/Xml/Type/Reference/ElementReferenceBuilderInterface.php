<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ElementReferenceBuilderInterface extends ElementReferenceCollectionBuilderInterface
{
    public function build(): ElementReferenceInterface;
}

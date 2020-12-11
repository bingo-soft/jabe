<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ElementReferenceBuilderInterface extends ElementReferenceCollectionBuilder
{
    public function build(): ElementReferenceInterface;
}

<?php

namespace Jabe\Model\Xml\Type\Reference;

use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

interface ElementReferenceBuilderInterface extends ElementReferenceCollectionBuilderInterface
{
    public function build(): ElementReferenceInterface;
}

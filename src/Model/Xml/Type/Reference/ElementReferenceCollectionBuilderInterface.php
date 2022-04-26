<?php

namespace Jabe\Model\Xml\Type\Reference;

use Jabe\Model\Xml\Impl\ModelBuildOperationInterface;

interface ElementReferenceCollectionBuilderInterface extends ModelBuildOperationInterface
{
    public function build(): ElementReferenceCollectionInterface;
}

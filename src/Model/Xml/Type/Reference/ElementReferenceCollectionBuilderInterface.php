<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

use BpmPlatform\Model\Xml\Impl\ModelBuildOperationInterface;

interface ElementReferenceCollectionBuilderInterface extends ModelBuildOperationInterface
{
    public function build(): ElementReferenceCollectionInterface;
}

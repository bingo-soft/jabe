<?php

namespace BpmPlatform\Model\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Xml\TestModel\TestModelConstants;

class Mother extends AnimalReference
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            Mother::class,
            TestModelConstants::ELEMENT_NAME_MOTHER
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->extendsType(AnimalReference::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): Mother
                {
                    return new Mother($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

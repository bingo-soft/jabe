<?php

namespace Tests\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Tests\Xml\TestModel\TestModelConstants;

class Guardian extends AnimalReference
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            Guardian::class,
            TestModelConstants::ELEMENT_NAME_GUARDIAN
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->extendsType(AnimalReference::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): Guardian
                {
                    return new Guardian($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

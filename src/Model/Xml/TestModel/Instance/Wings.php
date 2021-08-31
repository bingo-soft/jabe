<?php

namespace BpmPlatform\Model\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Xml\TestModel\TestModelConstants;

class Wings extends ModelElementInstanceImpl
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            Wings::class,
            TestModelConstants::TYPE_NAME_WINGS
        )
        ->namespaceUri(TestModelConstants::NEWER_NAMESPACE)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): Wings
                {
                    return new Wings($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

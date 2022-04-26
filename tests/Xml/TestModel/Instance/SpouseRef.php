<?php

namespace Tests\Xml\TestModel\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Tests\Xml\TestModel\TestModelConstants;

class SpouseRef extends ModelElementInstanceImpl
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            SpouseRef::class,
            TestModelConstants::ELEMENT_NAME_SPOUSE_REF
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): SpouseRef
                {
                    return new SpouseRef($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

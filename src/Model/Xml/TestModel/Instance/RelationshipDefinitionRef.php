<?php

namespace BpmPlatform\Model\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\TestModel\TestModelConstants;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;

class RelationshipDefinitionRef extends ModelElementInstanceImpl
{
    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            RelationshipDefinitionRef::class,
            TestModelConstants::ELEMENT_NAME_RELATIONSHIP_DEFINITION_REF
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): RelationshipDefinitionRef
                {
                    return new RelationshipDefinitionRef($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }
}

<?php

namespace Tests\Xml\TestModel\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Tests\Xml\TestModel\TestModelConstants;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;

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
            new class implements ModelTypeInstanceProviderInterface
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

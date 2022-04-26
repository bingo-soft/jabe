<?php

namespace Tests\Xml\TestModel\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Tests\Xml\TestModel\TestModelConstants;

class ChildRelationshipDefinition extends RelationshipDefinition
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ChildRelationshipDefinition::class,
            TestModelConstants::TYPE_NAME_CHILD_RELATIONSHIP_DEFINITION
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->extendsType(RelationshipDefinition::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ChildRelationshipDefinition
                {
                    return new ChildRelationshipDefinition($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

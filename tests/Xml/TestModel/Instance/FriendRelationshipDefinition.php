<?php

namespace Tests\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Tests\Xml\TestModel\TestModelConstants;

class FriendRelationshipDefinition extends RelationshipDefinition
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FriendRelationshipDefinition::class,
            TestModelConstants::TYPE_NAME_FRIEND_RELATIONSHIP_DEFINITION
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->extendsType(RelationshipDefinition::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): FriendRelationshipDefinition
                {
                    return new FriendRelationshipDefinition($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

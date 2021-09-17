<?php

namespace Tests\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Tests\Xml\TestModel\TestModelConstants;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;

abstract class RelationshipDefinition extends ModelElementInstanceImpl
{
    protected static $idAttr;
    protected static $animalRef;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            RelationshipDefinition::class,
            TestModelConstants::TYPE_NAME_RELATIONSHIP_DEFINITION
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->abstractType();

        self::$idAttr = $typeBuilder->stringAttribute(TestModelConstants::ATTRIBUTE_NAME_ID)
        ->idAttribute()
        ->build();

        self::$animalRef = $typeBuilder->stringAttribute(TestModelConstants::ATTRIBUTE_NAME_ANIMAL_REF)
        ->idAttributeReference(Animal::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getId(): string
    {
        return self::$idAttr->getValue($this);
    }

    public function setId(string $id): void
    {
        self::$idAttr->setValue($this, $id);
    }

    public function setAnimal(Animal $animalInRelationshipWith): void
    {
        self::$animalRef->setReferenceTargetElement($this, $animalInRelationshipWith);
    }

    public function getAnimal(): Animal
    {
        return self::$animalRef->getReferenceTargetElement($this);
    }
}

<?php

namespace BpmPlatform\Model\Xml\TestModel;

use BpmPlatform\Model\Xml\{
    ModelBuilder,
    ModelInterface
};
use BpmPlatform\Model\Xml\TestModel\Instance\{
    Animal,
    Animals,
    AnimalReference,
    Bird,
    ChildRelationshipDefinition,
    Description,
    FlightPartnerRef,
    FlyingAnimal,
    Guardian,
    GuardEgg,
    Mother,
    SpouseRef,
    FriendRelationshipDefinition,
    RelationshipDefinition,
    RelationshipDefinitionRef,
    Egg,
    FlightInstructor,
    Wings
};

class TestModel
{
    private static $model;
    private static $modelBuilder;

    public static function getTestModel(): ModelInterface
    {
        if (self::$model == null) {
            $modelBuilder = self::getModelBuilder();

            Animals::registerType($modelBuilder);
            Animal::registerType($modelBuilder);
            AnimalReference::registerType($modelBuilder);
            Bird::registerType($modelBuilder);
            ChildRelationshipDefinition::registerType($modelBuilder);
            Description::registerType($modelBuilder);
            FlightPartnerRef::registerType($modelBuilder);
            FlyingAnimal::registerType($modelBuilder);
            Guardian::registerType($modelBuilder);
            GuardEgg::registerType($modelBuilder);
            Mother::registerType($modelBuilder);
            SpouseRef::registerType($modelBuilder);
            FriendRelationshipDefinition::registerType($modelBuilder);
            RelationshipDefinition::registerType($modelBuilder);
            RelationshipDefinitionRef::registerType($modelBuilder);
            Egg::registerType($modelBuilder);
            FlightInstructor::registerType($modelBuilder);

            Wings::registerType($modelBuilder);

            self::$model = $modelBuilder->build();
        }

        return self::$model;
    }

    public static function getModelBuilder(): ModelBuilder
    {
        if (self::$modelBuilder == null) {
            self::$modelBuilder = ModelBuilder::createInstance(TestModelConstants::MODEL_NAME);
        }
        return self::$modelBuilder;
    }
}

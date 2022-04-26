<?php

namespace Tests\Xml\TestModel;

use Jabe\Model\Xml\{
    ModelBuilder,
    ModelInterface
};
use Tests\Xml\TestModel\Instance\{
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
            Description::registerType($modelBuilder);
            RelationshipDefinition::registerType($modelBuilder);
            RelationshipDefinitionRef::registerType($modelBuilder);
            FriendRelationshipDefinition::registerType($modelBuilder);
            ChildRelationshipDefinition::registerType($modelBuilder);
            AnimalReference::registerType($modelBuilder);
            FlightPartnerRef::registerType($modelBuilder);
            FlightInstructor::registerType($modelBuilder);
            FlyingAnimal::registerType($modelBuilder);
            Bird::registerType($modelBuilder);
            Guardian::registerType($modelBuilder);
            GuardEgg::registerType($modelBuilder);
            Mother::registerType($modelBuilder);
            SpouseRef::registerType($modelBuilder);
            Egg::registerType($modelBuilder);
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

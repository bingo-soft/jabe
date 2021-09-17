<?php

namespace Tests\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Tests\Xml\TestModel\TestModelConstants;

abstract class FlyingAnimal extends Animal
{
    public static $flightInstructorChild;
    public static $flightPartnerRefsColl;
    public static $wingspanAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FlyingAnimal::class,
            TestModelConstants::TYPE_NAME_FLYING_ANIMAL
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->extendsType(Animal::class)
        ->abstractType();

        self::$wingspanAttribute = $typeBuilder->doubleAttribute(TestModelConstants::ATTRIBUTE_NAME_WINGSPAN)
        ->build();

        $sequence = $typeBuilder->sequence();

        self::$flightInstructorChild = $sequence->element(FlightInstructor::class)
        ->idElementReference(FlyingAnimal::class)
        ->build();

        self::$flightPartnerRefsColl = $sequence->elementCollection(FlightPartnerRef::class)
        ->idElementReferenceCollection(FlyingAnimal::class)
        ->build();

        $typeBuilder->build();
    }

    public function getWingspan(): float
    {
        return self::$wingspanAttribute->getValue($this);
    }

    public function setWingspan(float $wingspan): void
    {
        self::$wingspanAttribute->setValue($this, $wingspan);
    }

    public function getFlightInstructor(): FlyingAnimal
    {
        return self::$flightInstructorChild->getReferenceTargetElement($this);
    }

    public function setFlightInstructor(FlyingAnimal $flightInstructor): void
    {
        self::$flightInstructorChild->setReferenceTargetElement($this, $flightInstructor);
    }

    public function removeFlightInstructor(): void
    {
        self::$flightInstructorChild->clearReferenceTargetElement($this);
    }

    public function getFlightPartnerRefs(): array
    {
        return self::$flightPartnerRefsColl->getReferenceTargetElements($this);
    }

    public function getFlightPartnerRefElements(): array
    {
        return self::$flightPartnerRefsColl->getReferenceSourceCollection()->get($this);
    }
}

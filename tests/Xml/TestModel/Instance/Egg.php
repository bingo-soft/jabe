<?php

namespace Tests\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Tests\Xml\TestModel\TestModelConstants;

class Egg extends ModelElementInstanceImpl
{
    protected static $idAttr;
    protected static $motherRefChild;
    protected static $guardianRefCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            Egg::class,
            TestModelConstants::ELEMENT_NAME_EGG
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): Egg
                {
                    return new Egg($instanceContext);
                }
            }
        );

        self::$idAttr = $typeBuilder->stringAttribute(TestModelConstants::ATTRIBUTE_NAME_ID)
        ->idAttribute()
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$motherRefChild = $sequenceBuilder->element(Mother::class)
        ->uriElementReference(Animal::class)
        ->build();

        self::$guardianRefCollection = $sequenceBuilder->elementCollection(Guardian::class)
        ->uriElementReferenceCollection(Animal::class)
        ->build();

        $typeBuilder->build();
    }

    public function getId(): string
    {
        return self::$idAttr->getValue($this);
    }

    public function setId(string $id): void
    {
        self::$idAttr->setValue($this, $id);
    }

    public function getMother(): ?Animal
    {
        return self::$motherRefChild->getReferenceTargetElement($this);
    }

    public function removeMother(): void
    {
        self::$motherRefChild->clearReferenceTargetElement($this);
    }

    public function setMother(Animal $mother): void
    {
        self::$motherRefChild->setReferenceTargetElement($this, $mother);
    }

    public function getMotherRef(): Mother
    {
        return self::$motherRefChild->getReferenceSource($this);
    }

    public function getGuardians(): array
    {
        return self::$guardianRefCollection->getReferenceTargetElements($this);
    }

    public function getGuardianRefs(): array
    {
        return self::$guardianRefCollection->getReferenceSourceCollection()->get($this);
    }

    public function addGuardian(Animal $guard): void
    {
        self::$guardianRefCollection->add($this, $guard);
    }

    public function addGuardianRef(Guardian $guard): void
    {
        self::$guardianRefCollection->getReferenceSourceCollection()->add($this, $guard);
    }

    public function removeGuardianRef(Guardian $guard): void
    {
        self::$guardianRefCollection->getReferenceSourceCollection()->remove($this, $guard);
    }

    public function clearGuardianRefs(): void
    {
        self::$guardianRefCollection->getReferenceSourceCollection()->clear($this);
    }
}

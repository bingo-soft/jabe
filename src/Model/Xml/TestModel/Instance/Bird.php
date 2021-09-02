<?php

namespace BpmPlatform\Model\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Xml\TestModel\TestModelConstants;

class Bird extends FlyingAnimal
{
    protected static $eggColl;
    protected static $spouseRefsColl;
    protected static $guardEggRefCollection;
    protected static $canHaveExtendedWings;
    protected static $wings;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            Bird::class,
            TestModelConstants::ELEMENT_NAME_BIRD
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->extendsType(FlyingAnimal::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): Bird
                {
                    return new Bird($instanceContext);
                }
            }
        );

        $sequence = $typeBuilder->sequence();

        self::$eggColl = $sequence->elementCollection(Egg::class)
        ->minOccurs(0)
        ->maxOccurs(6)
        ->build();

        self::$spouseRefsColl = $sequence->element(SpouseRef::class)
        ->qNameElementReference(Bird::class)
        ->build();

        self::$guardEggRefCollection = $sequence->elementCollection(GuardEgg::class)
        ->idsElementReferenceCollection(Egg::class)
        ->build();

        self::$canHaveExtendedWings = $typeBuilder->booleanAttribute("canHaveExtendedWings")
        ->namespace(TestModelConstants::NEWER_NAMESPACE)
        ->build();

        self::$wings = $sequence->element(Wings::class)
        ->build();

        $typeBuilder->build();
    }

    public function getEggs(): array
    {
        return self::$eggColl->get($this);
    }

    public function getSpouse(): Bird
    {
        return self::$spouseRefsColl->getReferenceTargetElement($this);
    }

    public function setSpouse(Bird $bird): void
    {
        self::$spouseRefsColl->setReferenceTargetElement($this, $bird);
    }

    public function removeSpouse(): void
    {
        self::$spouseRefsColl->clearReferenceTargetElement($this);
    }

    public function getSpouseRef(): SpouseRef
    {
        return self::$spouseRefsColl->getReferenceSource($this);
    }

    public function getGuardedEggs(): array
    {
        return self::$guardEggRefCollection->getReferenceTargetElements($this);
    }

    public function getGuardedEggRefs(): array
    {
        return self::$guardEggRefCollection->getReferenceSourceCollection()->get($this);
    }

    public function canHaveExtendedWings(): bool
    {
        return self::$canHaveExtendedWings->getValue($this);
    }

    public function setCanHaveExtendedWings(bool $b): void
    {
        self::$canHaveExtendedWings->setValue($this, $b);
    }

    public function getWings(): Wings
    {
        return self::$wings->getChild($this);
    }

    public function setWings(Wings $w): void
    {
        self::$wings->setChild($this, $w);
    }
}

<?php

namespace Tests\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Tests\Xml\TestModel\TestModelConstants;

class Animals extends ModelElementInstanceImpl
{
    protected static $descriptionChild;
    protected static $animalColl;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            Animals::class,
            TestModelConstants::ELEMENT_NAME_ANIMALS
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): Animals
                {
                    return new Animals($instanceContext);
                }
            }
        );

        $sequence = $typeBuilder->sequence();

        self::$descriptionChild = $sequence->element(Description::class)
        ->build();

        self::$animalColl = $sequence->elementCollection(Animal::class)
        ->build();

        $typeBuilder->build();
    }

    public function getDescription(): Description
    {
        return self::$descriptionChild->getChild(this);
    }

    public function setDescription(Description $description): void
    {
        self::$descriptionChild->setChild($this, $description);
    }

    public function getAnimals(): array
    {
        return self::$animalColl->get($this);
    }

    public function addAnimal(Animal $animal): void
    {
        self::$animalColl->add($this, $animal);
    }

    public function removeAnimal(Animal $animal): void
    {
        self::$animalColl->remove($this, $animal);
    }
}

<?php

namespace BpmPlatform\Model\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Xml\TestModel\TestModelConstants;

class AnimalReference extends ModelElementInstanceImpl
{
    protected static $hrefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getHref(): string
    {
        return self::$hrefAttribute->getValue($this);
    }

    public function setHref(string $href): void
    {
        self::$hrefAttribute->setValue($this, $href);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            AnimalReference::class,
            TestModelConstants::ELEMENT_NAME_ANIMAL_REFERENCE
        )
        ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): AnimalReference
                {
                    return new AnimalReference($instanceContext);
                }
            }
        );

        self::$hrefAttribute = $typeBuilder->stringAttribute("href")->required()->build();

        $typeBuilder->build();
    }
}

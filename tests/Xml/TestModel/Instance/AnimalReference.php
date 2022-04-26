<?php

namespace Tests\Xml\TestModel\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Tests\Xml\TestModel\TestModelConstants;

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
            new class implements ModelTypeInstanceProviderInterface
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

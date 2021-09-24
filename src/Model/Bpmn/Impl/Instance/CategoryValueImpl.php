<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    CategoryValueInterface,
    RootElementInterface
};

class CategoryValueImpl extends BaseElementImpl implements CategoryValueInterface
{
    protected static $valueAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CategoryValueInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CATEGORY_VALUE
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new CategoryValueImpl($instanceContext);
                }
            }
        );

        self::$valueAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_VALUE
        )->required()->build();

        $typeBuilder->build();
    }

    public function getValue(): string
    {
        return self::$valueAttribute->getValue($this);
    }

    public function setValue(string $name): void
    {
        self::$valueAttribute->setValue($this, $name);
    }
}

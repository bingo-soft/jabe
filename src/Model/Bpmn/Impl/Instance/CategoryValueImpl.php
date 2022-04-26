<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    CategoryValueInterface,
    BaseElementInterface
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
        ->extendsType(BaseElementInterface::class)
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
        )->build();

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

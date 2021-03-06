<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    DataInputInterface,
    ItemAwareElementInterface
};

class DataInputImpl extends ItemAwareElementImpl implements DataInputInterface
{
    protected static $nameAttribute;
    protected static $isCollectionAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DataInputInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_DATA_INPUT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ItemAwareElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DataInputImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$isCollectionAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_IS_COLLECTION)
        ->defaultValue(false)
        ->build();

        $typeBuilder->build();
    }

    public function getName(): string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function isCollection(): bool
    {
        return self::$isCollectionAttribute->getValue($this);
    }

    public function setCollection(bool $isCollection): void
    {
        self::$isCollectionAttribute->setValue($this, $isCollection);
    }
}

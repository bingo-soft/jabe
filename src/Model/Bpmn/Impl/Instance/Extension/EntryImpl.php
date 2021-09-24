<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Extension\EntryInterface;

class EntryImpl extends GenericValueElementImpl implements EntryInterface
{
    protected static $keyAttribute;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            EntryInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_ENTRY
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new EntryImpl($instanceContext);
                }
            }
        );

        self::$keyAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_KEY)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getKey(): string
    {
        return self::$keyAttribute->getValue($this);
    }

    public function setKey(string $key): void
    {
        self::$keyAttribute->setValue($this, $key);
    }
}

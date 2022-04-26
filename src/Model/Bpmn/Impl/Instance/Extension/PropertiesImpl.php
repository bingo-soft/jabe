<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\{
    PropertiesInterface,
    PropertyInterface
};

class PropertiesImpl extends BpmnModelElementInstanceImpl implements PropertiesInterface
{
    protected static $propertyCollection;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            PropertiesInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_PROPERTIES
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new PropertiesImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$propertyCollection = $sequenceBuilder->elementCollection(PropertyInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getProperties(): array
    {
        return self::$propertyCollection->get($this);
    }
}

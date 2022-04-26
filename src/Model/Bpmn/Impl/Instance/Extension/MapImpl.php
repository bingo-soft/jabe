<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\{
    EntryInterface,
    MapInterface
};

class MapImpl extends BpmnModelElementInstanceImpl implements MapInterface
{
    protected static $entryCollection;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            MapInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_MAP
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new MapImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$entryCollection = $sequenceBuilder->elementCollection(EntryInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getEntries(): array
    {
        return self::$entryCollection->get($this);
    }

    public function addEntry(EntryInterface $entry): void
    {
        self::$entryCollection->add($this, $entry);
    }
}

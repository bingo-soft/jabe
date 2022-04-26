<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Extension\{
    InputParameterInterface,
    ListInterface,
    MapInterface,
    ScriptInterface
};

class InputParameterImpl extends GenericValueElementImpl implements InputParameterInterface
{
    protected static $nameAttribute;
    protected static $scriptChild;
    protected static $listChild;
    protected static $mapChild;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            InputParameterInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_INPUT_PARAMETER
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new InputParameterImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_NAME)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->required()
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$scriptChild = $sequenceBuilder->element(ScriptInterface::class)
        ->build();

        self::$listChild = $sequenceBuilder->element(ListInterface::class)
        ->build();

        self::$mapChild = $sequenceBuilder->element(MapInterface::class)
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

    public function getScript(): ?ScriptInterface
    {
        return self::$scriptChild->getChild($this);
    }

    public function setScript(ScriptInterface $script): void
    {
        self::$scriptChild->setChild($this, $script);
    }

    public function getList(): ?ListInterface
    {
        return self::$listChild->getChild($this);
    }

    public function setList(ListInterface $list): void
    {
        self::$listChild->setChild($this, $list);
    }

    public function getMap(): ?MapInterface
    {
        return self::$mapChild->getChild($this);
    }

    public function setMap(MapInterface $map): void
    {
        self::$mapChild->setChild($this, $map);
    }
}

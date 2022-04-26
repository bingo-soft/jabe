<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\{
    ConnectorInterface,
    ConnectorIdInterface,
    InputOutputInterface
};

class ConnectorImpl extends BpmnModelElementInstanceImpl implements ConnectorInterface
{
    protected static $connectorIdChild;
    protected static $inputOutputChild;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ConnectorInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_CONNECTOR
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ConnectorImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$connectorIdChild = $sequenceBuilder->element(ConnectorIdInterface::class)
        ->required()
        ->build();

        self::$inputOutputChild = $sequenceBuilder->element(InputOutputInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getConnectorId(): ConnectorIdInterface
    {
        return self::$connectorIdChild->getChild($this);
    }

    public function setConnectorId(ConnectorIdInterface $connectorId): void
    {
        self::$connectorIdChild->setChild($this, $connectorId);
    }

    public function getInputOutput(): InputOutputInterface
    {
        return self::$inputOutputChild->getChild($this);
    }

    public function setInputOutput(InputOutputInterface $inputOutput): void
    {
        self::$inputOutputChild->setChild($this, $inputOutput);
    }
}

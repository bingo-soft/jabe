<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    DataAssociationInterface,
    DataOutputAssociationInterface
};

class DataOutputAssociationImpl extends DataAssociationImpl implements DataOutputAssociationInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DataOutputAssociationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_DATA_OUTPUT_ASSOCIATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(DataAssociationInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DataOutputAssociationImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

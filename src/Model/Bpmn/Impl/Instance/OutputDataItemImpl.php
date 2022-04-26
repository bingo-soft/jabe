<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    DataOutputInterface,
    OutputDataItemInterface
};

class OutputDataItemImpl extends DataOutputImpl implements OutputDataItemInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            OutputDataItemInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_OUTPUT_DATA_ITEM
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(DataOutputInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new OutputDataItemImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

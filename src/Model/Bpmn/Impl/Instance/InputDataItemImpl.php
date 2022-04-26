<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    DataInputInterface,
    InputDataItemInterface
};

class InputDataItemImpl extends DataInputImpl implements InputDataItemInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            InputDataItemInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_INPUT_DATA_ITEM
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(DataInputInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new InputDataItemImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

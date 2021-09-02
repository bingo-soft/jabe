<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
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

<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    ExpressionInterface,
    TimeDateInterface
};

class TimeDateImpl extends ExpressionImpl implements TimeDateInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            TimeDateInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_TIME_DATE
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ExpressionInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new TimeDateImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Extension\FailedJobRetryTimeCycleInterface;

class FailedJobRetryTimeCycleImpl extends BpmnModelElementInstanceImpl implements FailedJobRetryTimeCycleInterface
{
    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FailedJobRetryTimeCycleInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_FAILED_JOB_RETRY_TIME_CYCLE
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new FailedJobRetryTimeCycleImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}

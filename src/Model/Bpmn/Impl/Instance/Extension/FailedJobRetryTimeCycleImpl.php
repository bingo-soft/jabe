<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\FailedJobRetryTimeCycleInterface;

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

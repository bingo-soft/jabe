<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Tests\Bpmn\BpmnTestConstants;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\{
    Supports
};
use BpmPlatform\Model\Bpmn\Instance\{
    ArtifactInterface,
    AuditingInterface,
    CallableElementInterface,
    CorrelationSubscriptionInterface,
    FlowElementInterface,
    LaneSetInterface,
    MonitoringInterface,
    ProcessInterface,
    PropertyInterface,
    ResourceRoleInterface
};

class ProcessTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, CallableElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, AuditingInterface::class, 0, 1),
            new BpmnChildElementAssumption($this->model, MonitoringInterface::class, 0, 1),
            new BpmnChildElementAssumption($this->model, PropertyInterface::class),
            new BpmnChildElementAssumption($this->model, LaneSetInterface::class),
            new BpmnChildElementAssumption($this->model, FlowElementInterface::class),
            new BpmnChildElementAssumption($this->model, ArtifactInterface::class),
            new BpmnChildElementAssumption($this->model, ResourceRoleInterface::class),
            new BpmnChildElementAssumption($this->model, CorrelationSubscriptionInterface::class),
            new BpmnChildElementAssumption($this->model, Supports::class)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "processType", false, false, "None"),
            new AttributeAssumption(null, "isClosed", false, false, false),
            new AttributeAssumption(null, "isExecutable"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "candidateStarterGroups"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "candidateStarterUsers"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "jobPriority"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "taskPriority"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "historyTimeToLive"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "isStartableInTasklist", false, false, true),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "versionTag")
        ];
    }

    public function testJobPriority(): void
    {
        $process = $this->modelInstance->newInstance(ProcessInterface::class);
        $this->assertNull($process->getJobPriority());

        $process->setJobPriority("15");

        $this->assertEquals("15", $process->getJobPriority());
    }

    public function testTaskPriority(): void
    {
        $proc = $this->modelInstance->newInstance(ProcessInterface::class);
        $this->assertNull($proc->getTaskPriority());
        //when
        $proc->setTaskPriority(BpmnTestConstants::TEST_PROCESS_TASK_PRIORITY);
        //then
        $this->assertEquals(BpmnTestConstants::TEST_PROCESS_TASK_PRIORITY, $proc->getTaskPriority());
    }

    public function testHistoryTimeToLive(): void
    {
        //given
        $proc = $this->modelInstance->newInstance(ProcessInterface::class);
        $this->assertNull($proc->getHistoryTimeToLive());
        //when
        $proc->setHistoryTimeToLive(BpmnTestConstants::TEST_HISTORY_TIME_TO_LIVE);
        //then
        $this->assertEquals(BpmnTestConstants::TEST_HISTORY_TIME_TO_LIVE, $proc->getHistoryTimeToLive());
    }
}

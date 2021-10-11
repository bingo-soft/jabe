<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Bpmn\Bpmn;
use BpmPlatform\Model\Bpmn\Instance\{
    GatewayInterface,
    TaskInterface
};

class QueryTest extends TestCase
{
    private $modelInstance;
    private $startSucceeding;
    private $gateway1Succeeding;
    private $gateway2Succeeding;

    protected function setUp(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()->id("start")
        ->userTask()->id("user")
        ->parallelGateway()->id("gateway1")
          ->serviceTask()
          ->endEvent()
        ->moveToLastGateway()
          ->parallelGateway()->id("gateway2")
            ->userTask()
            ->endEvent()
          ->moveToLastGateway()
            ->serviceTask()
            ->endEvent()
          ->moveToLastGateway()
            ->scriptTask()
            ->endEvent()
        ->done();

        $this->startSucceeding = $this->modelInstance->getModelElementById("start")->getSucceedingNodes();
        $this->gateway1Succeeding = $this->modelInstance->getModelElementById("gateway1")->getSucceedingNodes();
        $this->gateway2Succeeding = $this->modelInstance->getModelElementById("gateway2")->getSucceedingNodes();
    }

    protected function tearDown(): void
    {
        Bpmn::getInstance()->validateModel($this->modelInstance);
    }

    public function testList(): void
    {
        $this->assertCount(1, $this->startSucceeding->list());
        $this->assertCount(2, $this->gateway1Succeeding->list());
        $this->assertCount(3, $this->gateway2Succeeding->list());
    }

    public function testCount(): void
    {
        $this->assertEquals(1, $this->startSucceeding->count());
        $this->assertEquals(2, $this->gateway1Succeeding->count());
        $this->assertEquals(3, $this->gateway2Succeeding->count());
    }

    public function testFilterByType(): void
    {
        $taskType = $this->modelInstance->getModel()->getType(TaskInterface::class);
        $gatewayType = $this->modelInstance->getModel()->getType(GatewayInterface::class);

        $this->assertCount(1, $this->startSucceeding->filterByType($taskType)->list());
        $this->assertCount(0, $this->startSucceeding->filterByType($gatewayType)->list());

        $this->assertCount(1, $this->gateway1Succeeding->filterByType($taskType)->list());
        $this->assertCount(1, $this->gateway1Succeeding->filterByType($gatewayType)->list());

        $this->assertCount(3, $this->gateway2Succeeding->filterByType($taskType)->list());
        $this->assertCount(0, $this->gateway2Succeeding->filterByType($gatewayType)->list());
    }

    public function testSingleResult(): void
    {
        $this->assertEquals("user", $this->startSucceeding->singleResult()->getId());

        try {
            $this->gateway1Succeeding->singleResult();
        } catch (\Exception $e) {
            $this->assertStringEndsWith("<2>", $e->getMessage());
        }
        try {
            $this->gateway2Succeeding->singleResult();
        } catch (\Exception $e) {
            $this->assertStringEndsWith("<3>", $e->getMessage());
        }
    }
}

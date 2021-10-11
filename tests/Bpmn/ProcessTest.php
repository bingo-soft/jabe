<?php

namespace Tests\Bpmn;

class ProcessTest extends BpmnModelTest
{
    protected function setUp(): void
    {
        parent::parseModel("ProcessTest.shouldImportProcess");
    }

    public function testShouldImportProcess(): void
    {
        $modelElementById = $this->bpmnModelInstance->getModelElementById("exampleProcessId");
        $this->assertFalse($modelElementById == null);

        $rootElements = $this->bpmnModelInstance->getDefinitions()->getRootElements();
        $this->assertCount(1, $rootElements);
        $process = $rootElements[0];

        $this->assertEquals("exampleProcessId", $process->getId());
        $this->assertTrue($process->getName() == null);
        $this->assertEquals("None", $process->getProcessType());
        $this->assertFalse($process->isExecutable());
        $this->assertFalse($process->isClosed());
    }
}

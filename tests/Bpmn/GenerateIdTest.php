<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Bpmn\Bpmn;
use BpmPlatform\Model\Bpmn\Instance\{
    ProcessInterface,
    StartEventInterface,
    UserTaskInterface,
    DefinitionsInterface,

};

class GenerateIdTest extends TestCase
{
    public function testShouldNotGenerateIdsOnRead(): void
    {
        $stream = fopen('tests/Bpmn/Resources/GenerateIdTest.bpmn', 'r+');
        $modelInstance = Bpmn::getInstance()->readModelFromStream($stream);
        $definitions = $modelInstance->getDefinitions();
        $this->assertNull($definitions->getId());

        $process = $modelInstance->getModelElementsByType(ProcessInterface::class)[0];
        $this->assertNull($process->getId());

        $startEvent = $modelInstance->getModelElementsByType(StartEventInterface::class)[0];
        $this->assertNull($startEvent->getId());

        $userTask = $modelInstance->getModelElementsByType(UserTaskInterface::class)[0];
        $this->assertNull($userTask->getId());
    }

    public function testShouldGenerateIdsOnCreate(): void
    {
        $modelInstance = Bpmn::getInstance()->createEmptyModel();
        $definitions = $modelInstance->newInstance(DefinitionsInterface::class);
        $this->assertFalse($definitions->getId() == null);

        $process = $modelInstance->newInstance(ProcessInterface::class);
        $this->assertFalse($process->getId() == null);

        $startEvent = $modelInstance->newInstance(StartEventInterface::class);
        $this->assertFalse($startEvent->getId() == null);

        $userTask = $modelInstance->newInstance(UserTaskInterface::class);
        $this->assertFalse($userTask->getId() == null);
    }
}

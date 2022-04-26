<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Bpmn\Instance\{
    HumanPerformerInterface,
    PerformerInterface,
    PotentialOwnerInterface
};

class ResourceRolesTest extends TestCase
{
    private $modelInstance;

    protected function setUp(): void
    {
        $stream = fopen('tests/Bpmn/Resources/ResourceRolesTest.bpmn', 'r+');
        $this->modelInstance = Bpmn::getInstance()->readModelFromStream($stream);
    }

    public function testGetPerformer(): void
    {
        $userTask = $this->modelInstance->getModelElementById("_3");
        $resourceRoles = $userTask->getResourceRoles();
        $this->assertCount(1, $resourceRoles);
        $resourceRole = $resourceRoles[0];
        $this->assertTrue($resourceRole instanceof PerformerInterface);
        $this->assertEquals("Task performer", $resourceRole->getName());
    }

    public function testGetHumanPerformer(): void
    {
        $userTask = $this->modelInstance->getModelElementById("_7");
        $resourceRoles = $userTask->getResourceRoles();
        $this->assertCount(1, $resourceRoles);
        $resourceRole = $resourceRoles[0];
        $this->assertTrue($resourceRole instanceof HumanPerformerInterface);
        $this->assertEquals("Task human performer", $resourceRole->getName());
    }

    public function testGetPotentialOwner(): void
    {
        $userTask = $this->modelInstance->getModelElementById("_9");
        $resourceRoles = $userTask->getResourceRoles();
        $this->assertCount(1, $resourceRoles);
        $resourceRole = $resourceRoles[0];
        $this->assertTrue($resourceRole instanceof PotentialOwnerInterface);
        $this->assertEquals("Task potential owner", $resourceRole->getName());
    }
}

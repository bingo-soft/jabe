<?php

namespace Tests\Bpmn\Event\Message;

use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\EventSubscriptionQueryImpl;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\DeleteJobsCmd;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface,
    CommandInvocationContext
};
use Jabe\Impl\Util\{
    ClockUtil,
    IoUtil
};
use Jabe\Test\Deployment;
use Jabe\Variable\{
    SerializationDataFormats,
    Variables
};
use Tests\Api\Variables\FailingPhpSerializable;
use Tests\Util\{
    PluggableProcessEngineTest,
    TestExecutionListener
};

class MessageIntermediateEventTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageIntermediateEventTest.testSingleIntermediateMessageEvent.bpmn20.xml"])]
    public function testSingleIntermediateMessageEvent(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("process");

        $activeActivityIds = $this->runtimeService->getActiveActivityIds($pi->getId());
        $this->assertFalse(empty($activeActivityIds));
        $this->assertEquals(1, count($activeActivityIds));
        $this->assertTrue(in_array("messageCatch", $activeActivityIds));

        $messageName = "newInvoiceMessage";
        $execution = $this->runtimeService->createExecutionQuery()
            ->processInstanceId($pi->getId())
            ->messageEventSubscriptionName($messageName)
            ->singleResult();

        $this->assertNotNull($execution);

        $this->runtimeService->messageEventReceived($messageName, $execution->getId());

        $task = $this->taskService->createTaskQuery()
            ->processInstanceId($pi->getId())
            ->singleResult();
        $this->assertNotNull($task);
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageIntermediateEventTest.testConcurrentIntermediateMessageEvent.bpmn20.xml"])]
    public function testConcurrentIntermediateMessageEvent(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("process");

        $activeActivityIds = $this->runtimeService->getActiveActivityIds($pi->getId());
        $this->assertFalse(empty($activeActivityIds));
        $this->assertEquals(2, count($activeActivityIds));
        $this->assertTrue(in_array("messageCatch1", $activeActivityIds));
        $this->assertTrue(in_array("messageCatch2", $activeActivityIds));

        $messageName = "newInvoiceMessage";
        $executions = $this->runtimeService->createExecutionQuery()
            ->processInstanceId($pi->getId())
            ->messageEventSubscriptionName($messageName)
            ->list();

        $this->assertFalse(empty($executions));
        $this->assertEquals(2, count($executions));

        $this->runtimeService->messageEventReceived($messageName, $executions[0]->getId());

        $task = $this->taskService->createTaskQuery()
            ->processInstanceId($pi->getId())
            ->singleResult();
        $this->assertNull($task);

        $this->runtimeService->messageEventReceived($messageName, $executions[1]->getId());

        $task = $this->taskService->createTaskQuery()
            ->processInstanceId($pi->getId())
            ->singleResult();
        $this->assertNotNull($task);

        $this->taskService->complete($task->getId());
    }

    public function testIntermediateMessageEventRedeployment(): void
    {

        // deploy version 1
        $deployment1 = $this->repositoryService->createDeployment()
            ->addClasspathResource("tests/Resources/Bpmn/Event/Message/MessageIntermediateEventTest.testSingleIntermediateMessageEvent.bpmn20.xml")
            ->deploy();

        // now there is one process deployed
        $this->assertEquals(1, $this->repositoryService->createProcessDefinitionQuery()->deploymentId($deployment1->getId())->count());

        $pi = $this->runtimeService->startProcessInstanceByKey("process");

        $activeActivityIds = $this->runtimeService->getActiveActivityIds($pi->getId());
        $this->assertFalse(empty($activeActivityIds));
        $this->assertEquals(1, count($activeActivityIds));
        $this->assertTrue(in_array("messageCatch", $activeActivityIds));

        // deploy version 2
        $deployment2 = $this->repositoryService->createDeployment()
            ->addClasspathResource("tests/Resources/Bpmn/Event/Message/MessageIntermediateEventTest.testSingleIntermediateMessageEvent.bpmn20.xml")
            ->deploy();

        // now there are two versions deployed:
        $this->assertEquals(1, $this->repositoryService->createProcessDefinitionQuery()->deploymentId($deployment2->getId())->count());

        // assert process is still waiting in message event:
        $activeActivityIds = $this->runtimeService->getActiveActivityIds($pi->getId());
        $this->assertFalse(empty($activeActivityIds));
        $this->assertEquals(1, count($activeActivityIds));
        $this->assertTrue(in_array("messageCatch", $activeActivityIds));
    }

    public function testEmptyMessageNameFails(): void
    {
        $this->expectException(\Exception::class);
        $this->repositoryService
            ->createDeployment()
            ->addClasspathResource("tests/Resources/Bpmn/Event/Message/MessageIntermediateEventTest.testEmptyMessageNameFails.bpmn20.xml")
            ->deploy();
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageIntermediateEventTest.testSingleIntermediateMessageEvent.bpmn20.xml"])]
    public function testSetSerializedVariableValues(): void
    {

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $messageEventSubscription = $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->singleResult();
        // when
        $phpSerializable = new FailingPhpSerializable("foo");

        $serializedObject = str_replace('\\', '.', serialize($phpSerializable));

        // but it can be set as a variable when delivering a message:
        $this->runtimeService
            ->messageEventReceived(
                "newInvoiceMessage",
                $messageEventSubscription->getExecutionId(),
                ["var" => Variables::serializedObjectValue($serializedObject)
                        ->objectTypeName(FailingPhpSerializable::class)
                        ->serializationDataFormat(SerializationDataFormats::PHP)
                        ->create()
                ]
            );
        // then
        $variableTyped = $this->runtimeService->getVariableTyped($processInstance->getId(), "var", false);
        $this->assertNotNull($variableTyped);
        $this->assertFalse($variableTyped->isDeserialized());
        $this->assertEquals($serializedObject, $variableTyped->getValueSerialized());
        $this->assertEquals(FailingPhpSerializable::class, $variableTyped->getObjectTypeName());
        $this->assertEquals(SerializationDataFormats::PHP, $variableTyped->getSerializationDataFormat());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageIntermediateEventTest.testExpressionInSingleIntermediateMessageEvent.bpmn20.xml"])]
    public function testExpressionInSingleIntermediateMessageEvent(): void
    {
        // given
        $variables = ["foo" => "bar"];

        // when
        $pi = $this->runtimeService->startProcessInstanceByKey("process", $variables);
        $activeActivityIds = $this->runtimeService->getActiveActivityIds($pi->getId());
        $this->assertFalse(empty($activeActivityIds));
        $this->assertEquals(1, count($activeActivityIds));
        $this->assertTrue(in_array("messageCatch", $activeActivityIds));

        // then
        $messageName = "newInvoiceMessage-bar";
        $execution = $this->runtimeService->createExecutionQuery()
            ->processInstanceId($pi->getId())
            ->messageEventSubscriptionName($messageName)
            ->singleResult();
        $this->assertNotNull($execution);

        $this->runtimeService->messageEventReceived($messageName, $execution->getId());
        $task = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())
            ->singleResult();
        $this->assertNotNull($task);
        $this->taskService->complete($task->getId());
    }
}

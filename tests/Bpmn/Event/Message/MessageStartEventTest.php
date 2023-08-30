<?php

namespace Tests\Bpmn\Event\Message;

use Jabe\ProcessEngineConfiguration;
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
use Jabe\Variable\Variables;
use Tests\Util\PluggableProcessEngineTest;

class MessageStartEventTest extends PluggableProcessEngineTest
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

    public function testDeploymentCreatesSubscriptions(): void
    {
        $deploymentId = $this->repositoryService
            ->createDeployment()
            ->addClasspathResource("tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testSingleMessageStartEvent3.bpmn20.xml")
            ->deploy()
            ->getId();

        $eventSubscriptions = $this->runtimeService->createEventSubscriptionQuery()->eventName("newInvoiceMessage3")->list();

        $this->assertEquals(1, count($eventSubscriptions));

        $this->repositoryService->deleteDeployment($deploymentId);
    }

    public function testSameMessageNameFails(): void
    {
        $deployment1 = $this->repositoryService
            ->createDeployment()
            ->addClasspathResource("tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testSingleMessageStartEvent.bpmn20.xml")
            ->deploy()
            ->getId();
        try {
            $deployment2 = $this->repositoryService
                ->createDeployment()
                ->addClasspathResource("tests/Resources/Bpmn/Event/Message/otherProcessWithNewInvoiceMessage.bpmn20.xml")
                ->deploy();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        } finally {
            // clean db:
            $this->repositoryService->deleteDeployment($deployment1, true);
            // Workaround for #CAM-4250: remove process definition of failed
            // deployment from deployment cache
            $this->processEngineConfiguration->getDeploymentCache()->getProcessDefinitionCache()->clear();
        }
    }

    public function testEmptyMessageNameFails(): void
    {
        $this->expectException(\Exception::class);
        $this->repositoryService
                ->createDeployment()
                ->addClasspathResource("tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testEmptyMessageNameFails.bpmn20.xml")
                ->deploy();
    }

    public function testSameMessageNameInSameProcessFails(): void
    {
        $this->expectException(\Exception::class);
        $this->repositoryService
                ->createDeployment()
                ->addClasspathResource("tests/Resources/Bpmn/Event/Message/testSameMessageNameInSameProcessFails.bpmn20.xml")
                ->deploy();
    }

    public function testUpdateProcessVersionCancelsSubscriptions(): void
    {
        $deploymentId = $this->repositoryService
            ->createDeployment()
            ->addClasspathResource("tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testSingleMessageStartEvent2.bpmn20.xml")
            ->deploy()
            ->getId();

        $eventSubscriptions = $this->runtimeService->createEventSubscriptionQuery()->eventName("newInvoiceMessage2")->list();
        $processDefinitions = $this->repositoryService->createProcessDefinitionQuery()->processDefinitionKey("singleMessageStartEvent2")->list();

        $this->assertEquals(1, count($eventSubscriptions));
        $this->assertEquals(1, count($processDefinitions));

        $newDeploymentId = $this->repositoryService
            ->createDeployment()
            ->addClasspathResource("tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testSingleMessageStartEvent2.bpmn20.xml")
            ->deploy()
            ->getId();

        $newEventSubscriptions = $this->runtimeService->createEventSubscriptionQuery()->eventName("newInvoiceMessage2")->list();
        $newProcessDefinitions = $this->repositoryService->createProcessDefinitionQuery()->processDefinitionKey("singleMessageStartEvent2")->list();

        $this->assertEquals(1, count($newEventSubscriptions));
        $this->assertEquals(2, count($newProcessDefinitions));
        foreach ($newProcessDefinitions as $processDefinition) {
            if ($processDefinition->getVersion() == 1) {
                foreach ($newEventSubscriptions as $subscription) {
                    $subscriptionEntity = $subscription;
                    $this->assertFalse($subscriptionEntity->getConfiguration() == $processDefinition->getId());
                }
            } else {
                foreach ($newEventSubscriptions as $subscription) {
                    $subscriptionEntity = $subscription;
                    $this->assertTrue($subscriptionEntity->getConfiguration() == $processDefinition->getId());
                }
            }
        }
        $this->assertFalse($eventSubscriptions == $newEventSubscriptions);

        $this->repositoryService->deleteDeployment($deploymentId);
        $this->repositoryService->deleteDeployment($newDeploymentId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testSingleMessageStartEvent.bpmn20.xml"])]
    public function testSingleMessageStartEvent(): void
    {
        // using startProcessInstanceByMessage triggers the message start event

        $processInstance = $this->runtimeService->startProcessInstanceByMessage("newInvoiceMessage");

        $this->assertFalse($processInstance->isEnded());

        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($task);

        $this->taskService->complete($task->getId());

        $this->testRule->assertProcessEnded($processInstance->getId());

        // using startProcessInstanceByKey also triggers the message event, if there is a single start event

        $processInstance = $this->runtimeService->startProcessInstanceByKey("singleMessageStartEvent");

        $this->assertFalse($processInstance->isEnded());

        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($task);

        $this->taskService->complete($task->getId());

        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testMessageStartEventAndNoneStartEvent.bpmn20.xml"])]
    public function testMessageStartEventAndNoneStartEvent(): void
    {
        // using startProcessInstanceByKey triggers the none start event

        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        $this->assertFalse($processInstance->isEnded());

        $task =  $this->taskService->createTaskQuery()->taskDefinitionKey("taskAfterNoneStart")->singleResult();
        $this->assertNotNull($task);

        $this->taskService->complete($task->getId());

        $this->testRule->assertProcessEnded($processInstance->getId());

        // using startProcessInstanceByMessage triggers the message start event

        $processInstance = $this->runtimeService->startProcessInstanceByMessage("newInvoiceMessage");

        $this->assertFalse($processInstance->isEnded());

        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("taskAfterMessageStart")->singleResult();
        $this->assertNotNull($task);

        $this->taskService->complete($task->getId());

        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testMultipleMessageStartEvents.bpmn20.xml"])]
    public function testMultipleMessageStartEvents(): void
    {

        // sending newInvoiceMessage

        $processInstance = $this->runtimeService->startProcessInstanceByMessage("newInvoiceMessage");

        $this->assertFalse($processInstance->isEnded());

        $task =  $this->taskService->createTaskQuery()->taskDefinitionKey("taskAfterMessageStart")->singleResult();
        $this->assertNotNull($task);

        $this->taskService->complete($task->getId());

        $this->testRule->assertProcessEnded($processInstance->getId());

        // sending newInvoiceMessage2

        $processInstance = $this->runtimeService->startProcessInstanceByMessage("newInvoiceMessage2");

        $this->assertFalse($processInstance->isEnded());

        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("taskAfterMessageStart2")->singleResult();
        $this->assertNotNull($task);

        $this->taskService->complete($task->getId());

        $this->testRule->assertProcessEnded($processInstance->getId());

        // starting the process using startProcessInstanceByKey is not possible:
        $this->expectException(\Exception::class);
        $this->runtimeService->startProcessInstanceByKey("testProcess");
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testDeployStartAndIntermediateEventWithSameMessageInSameProcess.bpmn"])]
    public function testDeployStartAndIntermediateEventWithSameMessageInSameProcess(): void
    {
        $pi = null;
        try {
            $this->runtimeService->startProcessInstanceByMessage("message010");
            $pi = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("testProcess01")->singleResult();
            $this->assertFalse($pi->isEnded());

            $deploymentId = $this->repositoryService
                ->createDeployment()
                ->addClasspathResource(
                    "tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testDeployStartAndIntermediateEventWithSameMessageInSameProcess.bpmn"
                )->name("deployment2")->deploy()->getId();
            $this->assertFalse($this->repositoryService->createDeploymentQuery()->deploymentId($deploymentId)->singleResult() == null);
        } finally {
            // clean db:
            $this->runtimeService->deleteProcessInstance($pi->getId(), "failure");

            $this->processEngineConfiguration->getDeploymentCache()->getProcessDefinitionCache()->clear();
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testDeployStartAndIntermediateEventWithSameMessageDifferentProcesses.bpmn"])]
    public function testDeployStartAndIntermediateEventWithSameMessageDifferentProcessesFirstStartEvent(): void
    {
        $pi = null;
        try {
            $this->runtimeService->startProcessInstanceByMessage("message");
            $pi = $this->runtimeService->createProcessInstanceQuery()->singleResult();
            $this->assertFalse($pi->isEnded());

            $deploymentId = $this->repositoryService
                ->createDeployment()
                ->addClasspathResource(
                    "tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testDeployStartAndIntermediateEventWithSameMessageDifferentProcesses2.bpmn"
                )
                ->name("deployment2")->deploy()->getId();
            $this->assertFalse($this->repositoryService->createDeploymentQuery()->deploymentId($deploymentId)->singleResult() == null);
        } finally {
            // clean db:
            $this->runtimeService->deleteProcessInstance($pi->getId(), "failure");
            // Workaround for #CAM-4250: remove process definition of failed
            // deployment from deployment cache

            $this->processEngineConfiguration->getDeploymentCache()->getProcessDefinitionCache()->clear();
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testDeployStartAndIntermediateEventWithSameMessageDifferentProcesses2.bpmn"])]
    public function testDeployStartAndIntermediateEventWithSameMessageDifferentProcessesFirstIntermediateEvent(): void
    {
        $pi = null;
        try {
            $this->runtimeService->startProcessInstanceByKey("Process_2");
            $pi = $this->runtimeService->createProcessInstanceQuery()->singleResult();
            $this->assertFalse($pi->isEnded());

            $deploymentId = $this->repositoryService
                ->createDeployment()
                ->addClasspathResource(
                    "tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testDeployStartAndIntermediateEventWithSameMessageDifferentProcesses.bpmn"
                )
                ->name("deployment2")->deploy()->getId();
            $this->assertFalse($this->repositoryService->createDeploymentQuery()->deploymentId($deploymentId)->singleResult() == null);
        } finally {
            // clean db:
            $this->runtimeService->deleteProcessInstance($pi->getId(), "failure");
            // Workaround for #CAM-4250: remove process definition of failed
            // deployment from deployment cache

            $this->processEngineConfiguration->getDeploymentCache()->getProcessDefinitionCache()->clear();
        }
    }

    private function testUsingExpressionWithDollarTagInMessageStartEventNameThrowsException(): void
    {
        // given a process definition with a start message event that has a message name which contains an expression
        $processDefinition =
            "tests/Resources/Bpmn/Event/Message/" .
                "MessageStartEventTest.testUsingExpressionWithDollarTagInMessageStartEventNameThrowsException.bpmn20.xml";
        $this->expectException(\Exception:: class);
        $this->repositoryService
        ->createDeployment()
        ->addClasspathResource($processDefinition)
        ->deploy();
    }

    private function testUsingExpressionWithHashTagInMessageStartEventNameThrowsException(): void
    {
        // given a process definition with a start message event that has a message name which contains an expression
        $processDefinition =
            "tests/Resources/Bpmn/Event/Message/" .
                "MessageStartEventTest.testUsingExpressionWithHashTagInMessageStartEventNameThrowsException.bpmn20.xml";
        $this->expectException(\Exception:: class);
        $this->repositoryService
                ->createDeployment()
                ->addClasspathResource($processDefinition)
                ->deploy();
    }

    //test fix CAM-10819
    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageStartEventTest.testMessageStartEventUsingCorrelationEngine.bpmn"])]
    public function testMessageStartEventUsingCorrelationEngineAndLocalVariable(): void
    {
        // when
        // sending newCorrelationStartMessage using correlation engine
        $processInstance = $this->runtimeService->createMessageCorrelation("newCorrelationStartMessage")
                ->setVariableLocal("var", "value")
                ->correlateWithResult()->getProcessInstance();

        // then
        // ensure the variable is available
        $processInstanceValue = $this->runtimeService->getVariableLocal($processInstance->getId(), "var");
        $this->assertEquals("value", $processInstanceValue);

        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }
}

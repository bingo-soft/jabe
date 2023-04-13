<?php

namespace Tests\Bpmn\Event\Escalation;

use Bpmn\Bpmn;
use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Event\EventType;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface,
    CommandInvocationContext
};
use Jabe\Test\{
    Deployment,
    RequiredHistoryLevel
};
use Jabe\Variable\Variables;
use Tests\Util\PluggableProcessEngineTest;

class EscalationEventTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        //initialize all services
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testThrowEscalationEventFromEmbeddedSubprocess.bpmn20.xml"])]
    public function testThrowEscalationEventFromEmbeddedSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
        // the non-interrupting boundary event should catch the escalation event
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->count());
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->singleResult()->getId());
        // and continue the subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->count());
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->singleResult()->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testThrowEscalationEventHierarchical.bpmn20.xml"])]
    public function testThrowEscalationEventHierarchical(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
        // the non-interrupting boundary event inside the subprocess should catch the escalation event (and not the boundary event on process)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation inside subprocess")->count());
        // and continue the subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->count());

        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation inside subprocess")->singleResult()->getId());
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->singleResult()->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.nonInterruptingEscalationBoundaryEventOnCallActivity.bpmn20.xml"])]
    public function testThrowEscalationEventFromCallActivity(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess");
        // when throw an escalation event on called process
        // the non-interrupting boundary event on call activity should catch the escalation event
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("task after catched escalation")->count());
        $this->taskService->complete($this->taskService->createTaskQuery()->taskName("task after catched escalation")->singleResult()->getId());
        // and continue the called process
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("task after thrown escalation")->count());
        $this->taskService->complete($this->taskService->createTaskQuery()->taskName("task after thrown escalation")->singleResult()->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml"])]
    public function testThrowEscalationEventNotCaught(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwEscalationProcess");
        // when throw an escalation event

        // continue the process instance, no activity should catch the escalation event
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("task after thrown escalation")->count());
        $this->taskService->complete($this->taskService->createTaskQuery()->taskName("task after thrown escalation")->singleResult()->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testBoundaryEventWithEscalationCode.bpmn20.xml"])]
    public function testBoundaryEventWithEscalationCode(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess with escalationCode=1

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
        // the non-interrupting boundary event with escalationCode=1 should catch the escalation event
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation 1")->count());
        // and continue the subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->count());
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testBoundaryEventWithoutEscalationCode.bpmn20.xml"])]
    public function testBoundaryEventWithoutEscalationCode(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
        // the non-interrupting boundary event without escalationCode should catch the escalation event (and all other escalation events)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->count());
        // and continue the subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->count());
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testBoundaryEventWithEmptyEscalationCode.bpmn20.xml"])]
    public function testBoundaryEventWithEmptyEscalationCode(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
        // the non-interrupting boundary event with empty escalationCode should catch the escalation event (and all other escalation events)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->count());
        // and continue the subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->count());
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testBoundaryEventWithoutEscalationRef.bpmn20.xml"])]
    public function testBoundaryEventWithoutEscalationRef(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
        // the non-interrupting boundary event without escalationRef should catch the escalation event (and all other escalation events)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->count());
        // and continue the subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->count());
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testInterruptingEscalationBoundaryEventOnMultiInstanceSubprocess.bpmn20.xml"])]
    public function testInterruptingEscalationBoundaryEventOnMultiInstanceSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the multi-instance subprocess

        // the interrupting boundary event should catch the first escalation event and cancel all instances of the subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("task after catched escalation")->count());
        $task = $this->taskService->createTaskQuery()->taskName("task after catched escalation")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testNonInterruptingEscalationBoundaryEventOnMultiInstanceSubprocess.bpmn20.xml"])]
    public function testNonInterruptingEscalationBoundaryEventOnMultiInstanceSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the multi-instance subprocess

        // the non-interrupting boundary event should catch every escalation event
        $this->assertEquals(5, $this->taskService->createTaskQuery()->taskName("task after catched escalation")->count());
        // and continue the subprocess
        $this->assertEquals(5, $this->taskService->createTaskQuery()->taskName("task in subprocess")->count());
        $tasks = $this->taskService->createTaskQuery()->taskName("task after catched escalation")->list();
        $tasks = array_merge($tasks, $this->taskService->createTaskQuery()->taskName("task in subprocess")->list());
        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }
    }

    /**
     * current bug: default value of 'cancelActivity' is 'true'
     */
    private function testImplicitNonInterruptingEscalationBoundaryEvent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
        // the implicit non-interrupting boundary event ('cancelActivity' is not defined) should catch the escalation event
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->count());
        // and continue the subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testInterruptingEscalationBoundaryEvent.bpmn20.xml"])]
    public function testInterruptingEscalationBoundaryEvent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess

        // the interrupting boundary should catch the escalation event event and cancel the subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->count());
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.interruptingEscalationBoundaryEventOnCallActivity.bpmn20.xml" ])]
    public function testInterruptingEscalationBoundaryEventOnCallActivity(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess");
        // when throw an escalation event on called process

        // the interrupting boundary event on call activity should catch the escalation event and cancel the called process
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->count());
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testParallelEscalationEndEvent.bpmn20.xml"])]
    public function testParallelEscalationEndEvent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation end event inside the subprocess

        // the non-interrupting boundary event should catch the escalation event
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->count());
        // and continue the parallel flow in subprocess
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->count());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testEscalationEndEvent.bpmn20.xml"])]
    public function testEscalationEndEvent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation end event inside the subprocess

        // the subprocess should end and
        // the non-interrupting boundary event should catch the escalation event
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->count());
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testPropagateOutputVariablesWhileCatchEscalationOnCallActivity.bpmn20.xml"])]
    public function testPropagateOutputVariablesWhileCatchEscalationOnCallActivity(): void
    {
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess", $variables)->getId();
        // when throw an escalation event on called process

        // the non-interrupting boundary event on call activity should catch the escalation event
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("task after catched escalation")->count());
        // and set the output variable of the called process to the process
        $this->assertEquals(42, $this->runtimeService->getVariable($processInstanceId, "output"));

        $task = $this->taskService->createTaskQuery()->taskName("task after catched escalation")->singleResult();
        $this->taskService->complete($task->getId());

        $task = $this->taskService->createTaskQuery()->taskName("task after thrown escalation")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testPropagateOutputVariablesWhileCatchEscalationOnCallActivity.bpmn20.xml"])]
    public function testPropagateOutputVariablesTwoTimes(): void
    {
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess", $variables)->getId();
        // when throw an escalation event on called process

        $taskInSuperProcess = $this->taskService->createTaskQuery()->taskDefinitionKey("taskAfterCatchedEscalation")->singleResult();
        $this->assertNotNull($taskInSuperProcess);

        // (1) the variables has been passed for the first time (from sub process to super process)
        $this->assertEquals(42, $this->runtimeService->getVariable($processInstanceId, "output"));

        // change variable "input" in sub process
        $taskInSubProcess = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $processInstance = $this->runtimeService->setVariable($taskInSubProcess->getProcessInstanceId(), "input", 999);
        $this->taskService->complete($taskInSubProcess->getId());

        // (2) the variables has been passed for the second time (from sub process to super process)
        $this->assertEquals(999, $this->runtimeService->getVariable($processInstanceId, "output"));

        $this->taskService->complete($taskInSuperProcess->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testPropagateOutputVariablesWhileCatchInterruptingEscalationOnCallActivity.bpmn20.xml"])]
    public function testPropagateOutputVariablesWhileCatchInterruptingEscalationOnCallActivity(): void
    {
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess", $variables)->getId();
        // when throw an escalation event on called process

        // the interrupting boundary event on call activity should catch the escalation event
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("task after catched escalation")->count());
        // and set the output variable of the called process to the process
        $this->assertEquals(42, $this->runtimeService->getVariable($processInstanceId, "output"));

        $task = $this->taskService->createTaskQuery()->taskName("task after catched escalation")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testPropagateOutputVariablesWithoutCatchEscalation.bpmn20.xml"])]
    public function testPropagateOutputVariablesWithoutCatchEscalation(): void
    {
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess", $variables)->getId();
        // when throw an escalation event on called process

        // then the output variable of the called process should be set to the process
        // also if the escalation is not caught by the process
        $this->assertEquals(42, $this->runtimeService->getVariable($processInstanceId, "output"));
        $task = $this->taskService->createTaskQuery()->taskName("task after thrown escalation")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testRetrieveEscalationCodeVariableOnBoundaryEvent.bpmn20.xml"])]
    public function testRetrieveEscalationCodeVariableOnBoundaryEvent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess

        // the boundary event should catch the escalation event
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->singleResult();
        $this->assertNotNull($task);

        // and set the escalationCode of the escalation event to the declared variable
        $this->assertEquals("escalationCode", $this->runtimeService->getVariable($task->getExecutionId(), "escalationCodeVar"));
        $this->taskService->complete($task->getId());

        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testRetrieveEscalationCodeVariableOnBoundaryEventWithoutEscalationCode.bpmn20.xml"])]
    public function testRetrieveEscalationCodeVariableOnBoundaryEventWithoutEscalationCode(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("escalationProcess");
        // when throw an escalation event inside the subprocess

        // the boundary event without escalationCode should catch the escalation event
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task after catched escalation")->singleResult();
        $this->assertNotNull($task);

        // and set the escalationCode of the escalation event to the declared variable
        $this->assertEquals("escalationCode", $this->runtimeService->getVariable($task->getExecutionId(), "escalationCodeVar"));

        $this->taskService->complete($task->getId());

        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("task in subprocess")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testInterruptingRetrieveEscalationCodeInSuperProcess.bpmn20.xml"])]
    public function testInterruptingRetrieveEscalationCodeInSuperProcess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess");

        // the event subprocess without escalationCode should catch the escalation event
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("taskAfterCatchedEscalation")->singleResult();
        $this->assertNotNull($task);

        // and set the escalationCode of the escalation event to the declared variable
        $this->assertEquals("escalationCode", $this->runtimeService->getVariable($task->getExecutionId(), "escalationCodeVar"));
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testInterruptingRetrieveEscalationCodeInSuperProcessWithoutEscalationCode.bpmn20.xml"])]
    public function testInterruptingRetrieveEscalationCodeInSuperProcessWithoutEscalationCode(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess");

        // the event subprocess without escalationCode should catch the escalation event
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("taskAfterCatchedEscalation")->singleResult();
        $this->assertNotNull($task);

        // and set the escalationCode of the escalation event to the declared variable
        $this->assertEquals("escalationCode", $this->runtimeService->getVariable($task->getExecutionId(), "escalationCodeVar"));
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testNonInterruptingRetrieveEscalationCodeInSuperProcess.bpmn20.xml"])]
    public function testNonInterruptingRetrieveEscalationCodeInSuperProcess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess");

        // the event subprocess without escalationCode should catch the escalation event
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("taskAfterCatchedEscalation")->singleResult();
        $this->assertNotNull($task);

        // and set the escalationCode of the escalation event to the declared variable
        $this->assertEquals("escalationCode", $this->runtimeService->getVariable($task->getExecutionId(), "escalationCodeVar"));
        $this->taskService->complete($task->getId());

        $task = $this->taskService->createTaskQuery()->taskName("task after thrown escalation")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.throwEscalationEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.testNonInterruptingRetrieveEscalationCodeInSuperProcessWithoutEscalationCode.bpmn20.xml"])]
    public function testNonInterruptingRetrieveEscalationCodeInSuperProcessWithoutEscalationCode(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchEscalationProcess");

        // the event subprocess without escalationCode should catch the escalation event
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("taskAfterCatchedEscalation")->singleResult();
        $this->assertNotNull($task);

        // and set the escalationCode of the escalation event to the declared variable
        $this->assertEquals("escalationCode", $this->runtimeService->getVariable($task->getExecutionId(), "escalationCodeVar"));
        $this->taskService->complete($task->getId());

        $task = $this->taskService->createTaskQuery()->taskName("task after thrown escalation")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/testOutputVariablesWhileThrowEscalation.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.escalationParent.bpmn20.xml"])]
    public function testPropagateOutputVariablesWhileThrowEscalation(): void
    {
        // given
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("EscalationParentProcess", $variables)->getId();

        // when throw an escalation event on called process
        $id = $this->taskService->createTaskQuery()->taskName("ut2")->singleResult()->getId();
        $this->taskService->complete($id);

        // then
        $this->checkOutput($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/testOutputVariablesWhileThrowEscalationTwoLevels.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.escalationParent.bpmn20.xml"])]
    public function testPropagateOutputVariablesWhileThrowEscalationTwoLevels(): void
    {
        // given
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("EscalationParentProcess", $variables)->getId();

        // when throw an escalation event on called process
        $id = $this->taskService->createTaskQuery()->taskName("ut2")->singleResult()->getId();
        $this->taskService->complete($id);

        // then
        $this->checkOutput($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/testOutputVariablesWhileThrowEscalationThreeLevels.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.escalationParent.bpmn20.xml"])]
    public function testPropagateOutputVariablesWhileThrowEscalationThreeLevels(): void
    {
        // given
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("EscalationParentProcess", $variables)->getId();

        // when throw an escalation event on called process
        $id = $this->taskService->createTaskQuery()->taskName("ut2")->singleResult()->getId();
        $this->taskService->complete($id);

        // then
        $this->checkOutput($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/testOutputVariablesWhileThrowEscalationInSubProcess.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.escalationParent.bpmn20.xml"])]
    public function testPropagateOutputVariablesWhileThrowEscalationInSubProcess(): void
    {
        // given
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("EscalationParentProcess", $variables)->getId();

        // when throw an escalation event on called process
        $id = $this->taskService->createTaskQuery()->taskName("ut2")->singleResult()->getId();
        $this->taskService->complete($id);

        // then
        $this->checkOutput($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/testOutputVariablesWhileThrowEscalationInSubProcessThreeLevels.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.escalationParent.bpmn20.xml"])]
    public function testPropagateOutputVariablesWhileThrowEscalationInSubProcessThreeLevels(): void
    {
        // given
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("EscalationParentProcess", $variables)->getId();

        // when throw an escalation event on called process
        $id = $this->taskService->createTaskQuery()->taskName("ut2")->singleResult()->getId();
        $this->taskService->complete($id);

        // then
        $this->checkOutput($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Escalation/testOutputVariablesWhileThrowEscalation2.bpmn20.xml", "tests/Resources/Bpmn/Event/Escalation/EscalationEventTest.escalationParent.bpmn20.xml"])]
    public function testPropagateOutputVariablesWhileThrowEscalation2(): void
    {
        // given
        $variables = [];
        $variables["input"] = 42;
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("EscalationParentProcess", $variables)->getId();

        // when throw an escalation event on called process
        $id = $this->taskService->createTaskQuery()->taskName("inside subprocess")->singleResult()->getId();
        $this->taskService->complete($id);

        // then
        $this->checkOutput($processInstanceId);
    }

    protected function checkOutput(string $processInstanceId): void
    {
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("task after catched escalation")->count());
        // and set the output variable of the called process to the process
        $this->assertNotNull($this->runtimeService->getVariable($processInstanceId, "cancelReason"));
        $this->assertEquals(42, $this->runtimeService->getVariable($processInstanceId, "output"));
        $id = $this->taskService->createTaskQuery()->taskName("task after catched escalation")->singleResult()->getId();
        $this->taskService->complete($id);
    }
}

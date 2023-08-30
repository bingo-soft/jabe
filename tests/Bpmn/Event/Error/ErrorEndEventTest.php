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

class ErrorEndEventTest extends PluggableProcessEngineTest
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

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Error/testPropagateOutputVariablesWhileThrowError.bpmn20.xml", "tests/Resources/Bpmn/Event/Error/ErrorEventTest.errorParent.bpmn20.xml"])]
    public function testPropagateOutputVariablesWhileThrowError(): void
    {
        // given
        $variables = ["input" => 42];
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("ErrorParentProcess", $variables)->getId();

        // when
        $id = $this->taskService->createTaskQuery()->taskName("ut2")->singleResult()->getId();
        $this->taskService->complete($id);

        // then
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->taskName("task after catched error")->count());
        // and set the output variable of the called process to the process
        $this->assertNotNull($this->runtimeService->getVariable($processInstanceId, "cancelReason"));
        $this->assertEquals(42, $this->runtimeService->getVariable($processInstanceId, "output"));
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Error/ErrorEndEventTest.testErrorMessage.bpmn20.xml"])]
    public function testErrorMessage(): void
    {
        // given a process definition including an error with camunda:errorMessage property
        $instance = $this->runtimeService->startProcessInstanceByKey("testErrorMessage");

        // when
        $variables = $this->runtimeService->getVariables($instance->getId());

        // then the error message defined in XML is accessible
        $this->assertEquals("123", $variables->get("errorCode"));
        $this->assertEquals("This is the error message indicating what went wrong.", $variables->get("errorMessage"));
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Error/ErrorEndEventTest.testErrorMessageExpression.bpmn20.xml"])]
    public function testErrorMessageExpression(): void
    {
        // given a process definition including an error with camunda:errorMessage property with an expression value
        $errorMessage = "This is the error message indicating what went wrong.";
        $initialVariables = ["errorMessageExpression" => $errorMessage];
        $instance = $this->runtimeService->startProcessInstanceByKey("testErrorMessageExpression", $initialVariables);

        // when
        $variables = $this->runtimeService->getVariables($instance->getId());

        // then the error message expression is resolved
        $this->assertEquals("123", $variables->get("errorCode"));
        $this->assertEquals($errorMessage, $variables->get("errorMessage"));
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Error/ErrorEndEventTest.testError.bpmn20.xml"])]
    public function testError(): void
    {
        // given a process definition including an error
        $instance = $this->runtimeService->startProcessInstanceByKey("testError");

        // when
        $variables = $this->runtimeService->getVariables($instance->getId());

        // then the error message defined in XML is accessible
        $this->assertEquals("123", $variables->get("errorCode"));
        $this->assertNull($variables->get("errorMessage"));
    }
}

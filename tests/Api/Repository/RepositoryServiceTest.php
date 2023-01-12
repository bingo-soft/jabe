<?php

namespace Tests\Api\Repository;

use PHPUnit\Framework\TestCase;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface
};
use Jabe\Impl\JobExecutor\TimerActivateProcessDefinitionHandler;
use Jabe\Impl\Test\TestHelper;
use Jabe\Test\{
    Deployment,
    RequiredHistoryLevel
};
use Tests\Util\PluggableProcessEngineTest;

class RepositoryServiceTest extends PluggableProcessEngineTest
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
        $commandExecutor = $this->processEngineConfiguration->getCommandExecutorTxRequired();
        $commandExecutor->execute(new class () implements CommandInterface {
            public function execute(CommandContext $commandContext)
            {
                $commandContext->getHistoricJobLogManager()->deleteHistoricJobLogsByHandlerType(TimerActivateProcessDefinitionHandler::TYPE);
                return null;
            }

            public function isRetryable(): bool
            {
                return false;
            }
        });
    }

    #[Deployment(resources: [ "tests/Resources/Api/oneTaskProcess.bpmn20.xml"])]
    public function testStartProcessInstanceById(): void
    {
        $processDefinitions = $this->repositoryService->createProcessDefinitionQuery()->list();
        $this->assertFalse(empty($processDefinitions));

        $processDefinition = $processDefinitions[0];
        $this->assertEquals("oneTaskProcess", $processDefinition->getKey());
        $this->assertNotNull($processDefinition->getId());

        $this->repositoryService->deleteDeployment($processDefinition->getDeploymentId(), true);
    }
}

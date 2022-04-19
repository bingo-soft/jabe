<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\Form\FormDataInterface;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class GetDeployedStartFormCmd extends AbstractGetDeployedFormCmd
{
    protected $processDefinitionId;

    public function __construct(?string $processDefinitionId)
    {
        EnsureUtil::ensureNotNull("Process definition id cannot be null", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
    }

    protected function getFormData(): FormDataInterface
    {
        $processDefinitionId = $this->processDefinitionId;
        $ctx = $this->commandContext;
        return $ctx->runWithoutAuthorization(function () use ($ctx, $processDefinitionId) {
            $cmd = new GetStartFormCmd($processDefinitionId);
            return $cmd->execute($ctx);
        });
    }

    protected function checkAuthorization(): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $deploymentCache = $processEngineConfiguration->getDeploymentCache();
        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($this->processDefinitionId);
        foreach ($this->commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessDefinition($processDefinition);
        }
    }
}

<?php

namespace Jabe\Impl\Cmd;

use Jabe\ScriptEvaluationException;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetRenderedStartFormCmd implements CommandInterface
{
    protected $processDefinitionId;
    protected $formEngineName;
    //private static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public function __construct(?string $processDefinitionId, ?string $engineName = null)
    {
        $this->processDefinitionId = $processDefinitionId;
        $this->formEngineName = $engineName;
    }

    public function __serialize(): array
    {
        return [
            'processDefinitionId' => $this->processDefinitionId,
            'formEngineName' => $this->formEngineName
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processDefinitionId = $data['processDefinitionId'];
        $this->formEngineName = $data['formEngineName'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $deploymentCache = $processEngineConfiguration->getDeploymentCache();
        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($this->processDefinitionId);
        EnsureUtil::ensureNotNull("Process Definition '" . $this->processDefinitionId . "' not found", "processDefinition", $processDefinition);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessDefinition($processDefinition);
        }

        $startFormHandler = $processDefinition->getStartFormHandler();
        if ($startFormHandler === null) {
            return null;
        }

        $formEngines = Context::getProcessEngineConfiguration()
            ->getFormEngines();
        $formEngine = null;
        if (array_key_exists($this->formEngineName, $formEngines)) {
            $formEngine = $formEngines[$this->formEngineName];
        }

        EnsureUtil::ensureNotNull("No formEngine '" . $this->formEngineName . "' defined process engine configuration", "formEngine", $formEngine);

        $startForm = $startFormHandler->createStartFormData($processDefinition);

        $renderedStartForm = null;
        try {
            $renderedStartForm = $formEngine->renderStartForm($startForm);
        } catch (ScriptEvaluationException $e) {
            //LOG.exceptionWhenStartFormScriptEvaluation(processDefinitionId, e);
        }
        return $renderedStartForm;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}

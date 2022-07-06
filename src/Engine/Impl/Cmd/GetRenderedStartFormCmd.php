<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ScriptEvaluationException;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetRenderedStartFormCmd implements CommandInterface, \Serializable
{
    protected $processDefinitionId;
    protected $formEngineName;
    //private static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public function __construct(string $processDefinitionId, ?string $engineName = null)
    {
        $this->processDefinitionId = $processDefinitionId;
        $this->formEngineName = $formEngineName;
    }

    public function serialize()
    {
        return json_encode([
            'processDefinitionId' => $this->processDefinitionId,
            'formEngineName' => $this->formEngineName
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processDefinitionId = $json->processDefinitionId;
        $this->formEngineName = $json->formEngineName;
    }

    public function execute(CommandContext $commandContext)
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
}

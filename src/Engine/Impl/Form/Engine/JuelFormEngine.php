<?php

namespace Jabe\Engine\Impl\Form\Engine;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Form\{
    FormDataInterface,
    StartFormDataInterface,
    TaskFormDataInterface
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Delegate\ScriptInvocation;
use Jabe\Engine\Impl\Persistence\Entity\{
    ResourceEntity,
    TaskEntity
};
use Jabe\Engine\Impl\Scripting\{
    ExecutableScript,
    ScriptFactory
};
use Jabe\Engine\Impl\Scripting\Engine\ScriptingEngines;
use Jabe\Engine\Impl\Util\EnsureUtil;

class JuelFormEngine implements FormEngineInterface
{
    public function getName(): string
    {
        return "juel";
    }

    public function renderStartForm(StartFormDataInterface $startForm)
    {
        if ($startForm->getFormKey() === null) {
            return null;
        }
        $formTemplateString = $this->getFormTemplateString($startForm, $startForm->getFormKey());
        return $this->executeScript($formTemplateString, null);
    }

    public function renderTaskForm(TaskFormDataInterface $taskForm)
    {
        if ($taskForm->getFormKey() === null) {
            return null;
        }
        $formTemplateString = $this->getFormTemplateString($taskForm, $taskForm->getFormKey());
        $task = $taskForm->getTask();
        return $this->executeScript($formTemplateString, $task->getExecution());
    }

    protected function executeScript(string $scriptSrc, VariableScopeInterface $scope)
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $scriptFactory = $processEngineConfiguration->getScriptFactory();
        $script = $scriptFactory->createScriptFromSource(ScriptingEngines::DEFAULT_SCRIPTING_LANGUAGE, $scriptSrc);

        $invocation = new ScriptInvocation($script, $scope);
        try {
            $processEngineConfiguration
            ->getDelegateInterceptor()
            ->handleInvocation($invocation);
        } catch (\Exception $e) {
            throw $e;
        }

        return $invocation->getInvocationResult();
    }

    protected function getFormTemplateString(FormDataInterface $formInstance, string $formKey): string
    {
        $deploymentId = $formInstance->getDeploymentId();

        $resourceStream = Context::getCommandContext()
            ->getResourceManager()
            ->findResourceByDeploymentIdAndResourceName($deploymentId, $formKey);

        EnsureUtil::ensureNotNull("Form with formKey '" . $formKey . "' does not exist", "resourceStream", $resourceStream);

        $resourceBytes = $resourceStream->getBytes();
        //$encoding = "UTF-8";
        $formTemplateString = "";
        try {
            $formTemplateString = $resourceBytes;
        } catch (\Exception $e) {
            throw $e;
        }
        return $formTemplateString;
    }
}

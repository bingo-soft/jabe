<?php

namespace Jabe\Impl;

use Jabe\Impl\Cmd\{
    CommandLogger,
    StartProcessInstanceAtActivitiesCmd,
    StartProcessInstanceCmd
};
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandExecutorInterface
};
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Runtime\{
    ProcessInstanceInterface,
    ProcessInstanceWithVariablesInterface,
    ProcessInstantiationBuilderInterface
};
use Jabe\Variable\VariableMapInterface;

class ProcessInstantiationBuilderImpl implements ProcessInstantiationBuilderInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $commandExecutor;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $businessKey;
    //protected $caseInstanceId;
    protected $tenantId;
    protected $processDefinitionTenantId;
    protected bool $isProcessDefinitionTenantIdSet = false;
    protected $modificationBuilder;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        $this->modificationBuilder = new ProcessInstanceModificationBuilderImpl();
        $this->commandExecutor = $commandExecutor;
    }

    public function startBeforeActivity(?string $activityId, ?string $ancestorActivityInstanceId = null): ProcessInstantiationBuilderInterface
    {
        $this->modificationBuilder->startBeforeActivity($activityId);
        return $this;
    }

    public function startAfterActivity(?string $activityId, ?string $ancestorActivityInstanceId = null): ProcessInstantiationBuilderInterface
    {
        $this->modificationBuilder->startAfterActivity($activityId);
        return $this;
    }

    public function startTransition(?string $transitionId, ?string $ancestorActivityInstanceId = null): ProcessInstantiationBuilderInterface
    {
        $this->modificationBuilder->startTransition($transitionId);
        return $this;
    }

    public function setVariable(?string $name, $value): ProcessInstantiationBuilderInterface
    {
        $this->modificationBuilder->setVariable($name, $value);
        return $this;
    }

    public function setVariableLocal(?string $name, $value): ProcessInstantiationBuilderInterface
    {
        $this->modificationBuilder->setVariableLocal($name, $value);
        return $this;
    }

    public function setVariables(array $variables): ProcessInstantiationBuilderInterface
    {
        if (!empty($variables)) {
            $this->modificationBuilder->setVariables($variables);
        }
        return $this;
    }

    public function setVariablesLocal(array $variables): ProcessInstantiationBuilderInterface
    {
        if (!empty($variables)) {
            $this->modificationBuilder->setVariablesLocal($variables);
        }
        return $this;
    }

    public function businessKey(?string $businessKey): ProcessInstantiationBuilderInterface
    {
        $this->businessKey = $businessKey;
        return $this;
    }

    /*public function caseInstanceId(?string $caseInstanceId): ProcessInstantiationBuilderInterface
    {
        $this->caseInstanceId = $caseInstanceId;
        return $this;
    }*/

    public function tenantId(?string $tenantId): ProcessInstantiationBuilderInterface
    {
        $this->tenantId = $tenantId;
        return $this;
    }

    public function processDefinitionTenantId(?string $tenantId): ProcessInstantiationBuilderInterface
    {
        $this->processDefinitionTenantId = $tenantId;
        $this->isProcessDefinitionTenantIdSet = true;
        return $this;
    }

    public function processDefinitionWithoutTenantId(): ProcessInstantiationBuilderInterface
    {
        $this->processDefinitionTenantId = null;
        $this->isProcessDefinitionTenantIdSet = true;
        return $this;
    }

    public function execute(bool $skipCustomListeners = false, bool $skipIoMappings = false, ...$args)
    {
        return $this->executeWithVariablesInReturn($skipCustomListeners, $skipIoMappings, ...$args);
    }

    public function executeWithVariablesInReturn(bool $skipCustomListeners = false, bool $skipIoMappings = false, ...$args): ProcessInstanceWithVariablesInterface
    {
        EnsureUtil::ensureOnlyOneNotNull("either process definition id or key must be set", $this->processDefinitionId, $this->processDefinitionKey);

        if ($this->isProcessDefinitionTenantIdSet && $this->processDefinitionId !== null) {
            //throw LOG.exceptionStartProcessInstanceByIdAndTenantId();
            throw new \Exception("exceptionStartProcessInstanceByIdAndTenantId");
        }

        $command = null;

        if (empty($this->modificationBuilder->getModificationOperations())) {
            if ($skipCustomListeners || $skipIoMappings) {
                //throw LOG.exceptionStartProcessInstanceAtStartActivityAndSkipListenersOrMapping();
                throw new \Exception("exceptionStartProcessInstanceAtStartActivityAndSkipListenersOrMapping");
            }
            // start at the default start activity
            $command = new StartProcessInstanceCmd($this);
        } else {
            // start at any activity using the instructions
            $this->modificationBuilder->setSkipCustomListeners($skipCustomListeners);
            $this->modificationBuilder->setSkipIoMappings($skipIoMappings);

            $command = new StartProcessInstanceAtActivitiesCmd($this);
        }

        return $this->commandExecutor->execute($command, ...$args);
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionKey(): ?string
    {
        return $this->processDefinitionKey;
    }

    public function getModificationBuilder(): ProcessInstanceModificationBuilderImpl
    {
        return $this->modificationBuilder;
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    /*public String getCaseInstanceId() {
        return caseInstanceId;
    }*/

    public function getVariables(): VariableMapInterface
    {
        return $this->modificationBuilder->getProcessVariables();
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getProcessDefinitionTenantId(): ?string
    {
        return $this->processDefinitionTenantId;
    }

    public function isProcessDefinitionTenantIdSet(): bool
    {
        return $this->isProcessDefinitionTenantIdSet;
    }

    public function setModificationBuilder(ProcessInstanceModificationBuilderImpl $modificationBuilder): void
    {
        $this->modificationBuilder = $modificationBuilder;
    }

    public static function createProcessInstanceById(CommandExecutorInterface $commandExecutor, ?string $processDefinitionId): ProcessInstantiationBuilderInterface
    {
        $builder = new ProcessInstantiationBuilderImpl($commandExecutor);
        $builder->processDefinitionId = $processDefinitionId;
        return $builder;
    }

    public static function createProcessInstanceByKey(CommandExecutorInterface $commandExecutor, ?string $processDefinitionKey): ProcessInstantiationBuilderInterface
    {
        $builder = new ProcessInstantiationBuilderImpl($commandExecutor);
        $builder->processDefinitionKey = $processDefinitionKey;
        return $builder;
    }
}

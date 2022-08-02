<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Cmd\{
    CommandLogger,
    CorrelateAllMessageCmd,
    CorrelateMessageCmd
};
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Runtime\{
    MessageCorrelationBuilderInterface,
    MessageCorrelationResultInterface,
    MessageCorrelationResultWithVariablesInterface,
    ProcessInstanceInterface
};
use Jabe\Engine\Variable\VariableMapInterface;
use Jabe\Engine\Variable\Impl\VariableMapImpl;

class MessageCorrelationBuilderImpl implements MessageCorrelationBuilderInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;
    protected $commandExecutor;
    protected $commandContext;
    protected $isExclusiveCorrelation = false;
    protected $messageName;
    protected $businessKey;
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $correlationProcessInstanceVariables;
    protected $correlationLocalVariables;
    protected $payloadProcessInstanceVariables;
    protected $payloadProcessInstanceVariablesLocal;
    protected $tenantId = null;
    protected $isTenantIdSet = false;
    protected $startMessagesOnly = false;
    protected $executionsOnly = false;

    public function __construct(/*CommandExecutor*/$commandOrMessage, string $messageName = null)
    {
        if ($commandOrMessage instanceof CommandExecutorInterface) {
            $this->messageName = $messageName;
            EnsureUtil::ensureNotNull("commandExecutor", "ommandExecutor", $commandOrMessage);
            $this->commandExecutor = $commandOrMessage;
        } elseif ($commandOrMessage instanceof CommandContext) {
            $this->messageName = $messageName;
            EnsureUtil::ensureNotNull("commandContext", "commandContext", $commandOrMessage);
            $this->commandContext = $commandOrMessage;
        } elseif (is_string($commandOrMessage)) {
            $this->messageName = $commandOrMessage;
        }
    }

    public function processInstanceBusinessKey(string $businessKey): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull("businessKey", "businessKey", $businessKey);
        $this->businessKey = $businessKey;
        return $this;
    }

    public function processInstanceVariableEquals(string $variableName, $variableValue): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        $this->ensureCorrelationProcessInstanceVariablesInitialized();
        $this->correlationProcessInstanceVariables->put($variableName, $variableValue);
        return $this;
    }

    public function processInstanceVariablesEqual(array $variables): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull("variables", "variables", $variables);
        $this->ensureCorrelationProcessInstanceVariablesInitialized();
        $this->correlationProcessInstanceVariables->putAll($variables);
        return $this;
    }

    public function localVariableEquals(string $variableName, $variableValue): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        $this->ensureCorrelationLocalVariablesInitialized();

        $this->correlationLocalVariables->put($variableName, $variableValue);
        return $this;
    }

    public function localVariablesEqual(array $variables): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull("variables", "variables", $variables);
        $this->ensureCorrelationLocalVariablesInitialized();

        $this->correlationLocalVariables->putAll($variables);
        return $this;
    }

    protected function ensureCorrelationProcessInstanceVariablesInitialized(): void
    {
        if ($this->correlationProcessInstanceVariables === null) {
            $this->correlationProcessInstanceVariables = new VariableMapImpl();
        }
    }

    protected function ensureCorrelationLocalVariablesInitialized(): void
    {
        if ($this->correlationLocalVariables === null) {
            $this->correlationLocalVariables = new VariableMapImpl();
        }
    }

    public function processInstanceId(string $id): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull("processInstanceId", "id", $id);
        $this->processInstanceId = $id;
        return $this;
    }

    public function processDefinitionId(string $processDefinitionId): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function setVariable(string $variableName, $variableValue): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        $this->ensurePayloadProcessInstanceVariablesInitialized();
        $this->payloadProcessInstanceVariables->put($variableName, $variableValue);
        return $this;
    }

    public function setVariableLocal(string $variableName, $variableValue): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        $this->ensurePayloadProcessInstanceVariablesLocalInitialized();
        $this->payloadProcessInstanceVariablesLocal->put($variableName, $variableValue);
        return $this;
    }

    public function setVariables(array $variables): MessageCorrelationBuilderInterface
    {
        if (!empty($variables)) {
            $this->ensurePayloadProcessInstanceVariablesInitialized();
            $this->payloadProcessInstanceVariables->putAll($variables);
        }
        return $this;
    }

    public function setVariablesLocal(array $variables): MessageCorrelationBuilderInterface
    {
        if (!empty($variables)) {
            $this->ensurePayloadProcessInstanceVariablesLocalInitialized();
            $this->payloadProcessInstanceVariablesLocal->putAll($variables);
        }
        return $this;
    }

    protected function ensurePayloadProcessInstanceVariablesInitialized(): void
    {
        if ($this->payloadProcessInstanceVariables === null) {
            $this->payloadProcessInstanceVariables = new VariableMapImpl();
        }
    }

    protected function ensurePayloadProcessInstanceVariablesLocalInitialized(): void
    {
        if ($this->payloadProcessInstanceVariablesLocal === null) {
            $this->payloadProcessInstanceVariablesLocal = new VariableMapImpl();
        }
    }

    public function tenantId(string $tenantId): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureNotNull(
            "The tenant-id cannot be null. Use 'withoutTenantId()' if you want to correlate the message to a process definition or an execution which has no tenant-id.",
            "tenantId",
            $tenantId
        );

        $this->isTenantIdSet = true;
        $this->tenantId = $tenantId;
        return $this;
    }

    public function withoutTenantId(): MessageCorrelationBuilderInterface
    {
        $this->isTenantIdSet = true;
        $this->tenantId = null;
        return $this;
    }

    public function startMessageOnly(): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureFalse("Either startMessageOnly or executionsOnly can be set", "executionsOnly", $this->executionsOnly);
        $this->startMessagesOnly = true;
        return $this;
    }

    public function executionsOnly(): MessageCorrelationBuilderInterface
    {
        EnsureUtil::ensureFalse("Either startMessageOnly or executionsOnly can be set", "startMessagesOnly", $this->startMessagesOnly);
        $this->executionsOnly = true;
        return $this;
    }

    public function correlate(): void
    {
        $this->correlateWithResult();
    }

    public function correlateWithResult(): MessageCorrelationResultInterface
    {
        if ($this->startMessagesOnly) {
            $this->ensureCorrelationVariablesNotSet();
            $this->ensureProcessDefinitionAndTenantIdNotSet();
        } else {
            $this->ensureProcessDefinitionIdNotSet();
            $this->ensureProcessInstanceAndTenantIdNotSet();
        }
        return $this->execute(new CorrelateMessageCmd($this, false, false, $this->startMessagesOnly));
    }

    public function correlateWithResultAndVariables(bool $deserializeValues): MessageCorrelationResultWithVariablesInterface
    {
        if ($this->startMessagesOnly) {
            $this->ensureCorrelationVariablesNotSet();
            $this->ensureProcessDefinitionAndTenantIdNotSet();
        } else {
            $this->ensureProcessDefinitionIdNotSet();
            $this->ensureProcessInstanceAndTenantIdNotSet();
        }
        return $this->execute(new CorrelateMessageCmd($this, true, $deserializeValues, $this->startMessagesOnly));
    }

    public function correlateExclusively(): void
    {
        $this->isExclusiveCorrelation = true;
        $this->correlate();
    }

    public function correlateAll(): void
    {
        $this->correlateAllWithResult();
    }

    public function correlateAllWithResult(): array
    {
        if ($this->startMessagesOnly) {
            $this->ensureCorrelationVariablesNotSet();
            $this->ensureProcessDefinitionAndTenantIdNotSet();
            // only one result can be expected
            $result = $this->execute(new CorrelateMessageCmd($this, false, false, $this->startMessagesOnly));
            return $result;
        } else {
            $this->ensureProcessDefinitionIdNotSet();
            $this->ensureProcessInstanceAndTenantIdNotSet();
            return $this->execute(new CorrelateAllMessageCmd($this, false, false));
        }
    }

    public function correlateAllWithResultAndVariables(bool $deserializeValues): array
    {
        if ($this->startMessagesOnly) {
            $this->ensureCorrelationVariablesNotSet();
            $this->ensureProcessDefinitionAndTenantIdNotSet();
            // only one result can be expected
            $result = $this->execute(new CorrelateMessageCmd($this, true, $deserializeValues, $this->startMessagesOnly));
            return $result;
        } else {
            $this->ensureProcessDefinitionIdNotSet();
            $this->ensureProcessInstanceAndTenantIdNotSet();
            return $this->execute(new CorrelateAllMessageCmd($this, true, $deserializeValues));
        }
    }

    public function correlateStartMessage(): ProcessInstanceInterface
    {
        $this->startMessageOnly();
        $result = $this->correlateWithResult();
        return $result->getProcessInstance();
    }

    protected function ensureProcessDefinitionIdNotSet(): void
    {
        if ($this->processDefinitionId !== null) {
            //throw LOG.exceptionCorrelateMessageWithProcessDefinitionId();
            throw new \Exception("exceptionCorrelateMessageWithProcessDefinitionId");
        }
    }

    protected function ensureProcessInstanceAndTenantIdNotSet(): void
    {
        if ($this->processInstanceId !== null && $this->isTenantIdSet) {
            //throw LOG.exceptionCorrelateMessageWithProcessInstanceAndTenantId();
            throw new \Exception("exceptionCorrelateMessageWithProcessInstanceAndTenantId");
        }
    }

    protected function ensureCorrelationVariablesNotSet(): void
    {
        if ($this->correlationProcessInstanceVariables !== null || $this->correlationLocalVariables !== null) {
            //throw LOG.exceptionCorrelateStartMessageWithCorrelationVariables();
            throw new \Exception("exceptionCorrelateStartMessageWithCorrelationVariables");
        }
    }

    protected function ensureProcessDefinitionAndTenantIdNotSet(): void
    {
        if ($this->processDefinitionId !== null && $this->isTenantIdSet) {
            //throw LOG.exceptionCorrelateMessageWithProcessDefinitionAndTenantId();
            throw new \Exception("exceptionCorrelateMessageWithProcessDefinitionAndTenantId");
        }
    }

    protected function execute(CommandInterface $command)
    {
        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->execute($command);
        } else {
            return $command->execute($this->commandContext);
        }
    }

    // getters //////////////////////////////////

    public function getCommandExecutor(): CommandExecutorInterface
    {
        return $this->commandExecutor;
    }

    public function getCommandContext(): ?CommandContext
    {
        return $this->commandContext;
    }

    public function getMessageName(): string
    {
        return $this->messageName;
    }

    public function getBusinessKey(): string
    {
        return $this->businessKey;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function getCorrelationProcessInstanceVariables(): VariableMapInterface
    {
        return $this->correlationProcessInstanceVariables;
    }

    public function getCorrelationLocalVariables(): VariableMapInterface
    {
        return $this->correlationLocalVariables;
    }

    public function getPayloadProcessInstanceVariables(): VariableMapInterface
    {
        return $this->payloadProcessInstanceVariables;
    }

    public function getPayloadProcessInstanceVariablesLocal(): VariableMapInterface
    {
        return $this->payloadProcessInstanceVariablesLocal;
    }

    public function isExclusiveCorrelation(): bool
    {
        return $this->isExclusiveCorrelation;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function isExecutionsOnly(): bool
    {
        return $this->executionsOnly;
    }
}

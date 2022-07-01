<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Cmd\{
    CommandLogger,
    SignalEventReceivedCmd
};
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Runtime\SignalEventReceivedBuilderInterface;
use Jabe\Engine\Variable\VariableMapInterface;
use Jabe\Engine\Variable\Impl\VariableMapImpl;

class SignalEventReceivedBuilderImpl implements SignalEventReceivedBuilderInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;
    protected $commandExecutor;
    protected $signalName;
    protected $executionId = null;
    protected $tenantId = null;
    protected $isTenantIdSet = false;

    protected $variables = null;

    public function __construct(CommandExecutorInterface $commandExecutor, string $signalName)
    {
        $this->commandExecutor = $commandExecutor;
        $this->signalName = $signalName;
    }

    public function setVariables(array $variables): SignalEventReceivedBuilder
    {
        if (!empty($variables)) {
            if (empty($this->variables)) {
                $this->variables = new VariableMapImpl();
            }
            $this->variables->putAll($variables);
        }
        return $this;
    }

    public function executionId(string $executionId): SignalEventReceivedBuilderInterface
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $executionId);
        $this->executionId = $executionId;
        return $this;
    }

    public function tenantId(string $tenantId): SignalEventReceivedBuilderInterface
    {
        EnsureUtil::ensureNotNull(
            "The tenant-id cannot be null. Use 'withoutTenantId()' if you want to send the signal to a process definition or an execution which has no tenant-id.",
            "tenantId",
            $tenantId
        );

        $this->tenantId = $tenantId;
        $this->isTenantIdSet = true;

        return $this;
    }

    public function withoutTenantId(): SignalEventReceivedBuilderInterface
    {
        // tenant-id is null
        $this->isTenantIdSet = true;
        return $this;
    }

    public function send(): void
    {
        if ($this->executionId != null && $this->isTenantIdSet) {
            //throw LOG.exceptionDeliverSignalToSingleExecutionWithTenantId();
            throw new \Exception("exceptionDeliverSignalToSingleExecutionWithTenantId");
        }

        $command = new SignalEventReceivedCmd($this);
        $this->commandExecutor->execute($command);
    }

    public function getSignalName(): string
    {
        return $this->signalName;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function getVariables(): VariableMapInterface
    {
        return $this->variables;
    }
}

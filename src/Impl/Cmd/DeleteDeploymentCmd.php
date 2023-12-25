<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\{
    TransactionLogger,
    TransactionState
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Deploy\DeleteDeploymentFailListener;
use Jabe\Impl\Persistence\Entity\{
    PropertyChange,
    UserOperationLogManager
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteDeploymentCmd implements CommandInterface
{
    //private final static TransactionLogger TX_LOG = ProcessEngineLogger.TX_LOGGER;

    protected $deploymentId;
    protected bool $cascade = false;
    protected bool $skipCustomListeners = false;
    protected bool $skipIoMappings = false;

    public function __construct(?string $deploymentId, bool $cascade, bool $skipCustomListeners, bool $skipIoMappings)
    {
        $this->deploymentId = $deploymentId;
        $this->cascade = $cascade;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMappings = $skipIoMappings;
    }

    public function __serialize(): array
    {
        return [
            'deploymentId' => $this->deploymentId,
            'cascade' => $this->cascade,
            'skipCustomListeners' => $this->skipCustomListeners,
            'skipIoMappings' => $this->skipIoMappings
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->deploymentId = $data['deploymentId'];
        $this->cascade = $data['cascade'];
        $this->skipCustomListeners = $data['skipCustomListeners'];
        $this->skipIoMappings = $data['skipIoMappings'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("deploymentId", "deploymentId", $this->deploymentId);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkDeleteDeployment($this->deploymentId);
        }

        $logManager = $commandContext->getOperationLogManager();
        $propertyChanges = [new PropertyChange("cascade", null, $this->cascade)];
        $logManager->logDeploymentOperation(UserOperationLogEntryInterface::OPERATION_TYPE_DELETE, $this->deploymentId, $propertyChanges);

        $commandContext
            ->getDeploymentManager()
            ->deleteDeployment($this->deploymentId, $this->cascade, $this->skipCustomListeners, $this->skipIoMappings);

        $processApplicationReference = Context::getProcessEngineConfiguration()
            ->getProcessApplicationManager()
            ->getProcessApplicationForDeployment($this->deploymentId);

        $listener = new DeleteDeploymentFailListener(
            $this->deploymentId,
            $processApplicationReference,
            Context::getProcessEngineConfiguration()->getCommandExecutorTxRequiresNew()
        );

        try {
            $deploymentId = $this->deploymentId;
            $commandContext->runWithoutAuthorization(function () use ($commandContext, $deploymentId) {
                $cmd = new UnregisterProcessApplicationCmd($deploymentId, false);
                $cmd->execute($commandContext);
            });
            $commandContext->runWithoutAuthorization(function () use ($commandContext, $deploymentId) {
                $cmd = new UnregisterDeploymentCmd([$deploymentId]);
                $cmd->execute($commandContext);
            });
        } finally {
            try {
                $commandContext->getTransactionContext()->addTransactionListener(TransactionState::ROLLED_BACK, $listener);
            } catch (\Throwable $e) {
                //TX_LOG.debugTransactionOperation("Could not register transaction synchronization. Probably the TX has already been rolled back by application code.");
                $listener->execute($commandContext);
            }
        }

        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}

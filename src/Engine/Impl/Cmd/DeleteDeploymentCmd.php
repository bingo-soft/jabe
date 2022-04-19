<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Application\ProcessApplicationReferenceInterface;
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Cfg\{
    TransactionLogger,
    TransactionState
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Deploy\DeleteDeploymentFailListener;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    PropertyChange,
    UserOperationLogManager
};

class DeleteDeploymentCmd implements CommandInterface, \Serializable
{
    //private final static TransactionLogger TX_LOG = ProcessEngineLogger.TX_LOGGER;

    protected $deploymentId;
    protected $cascade;

    protected $skipCustomListeners;
    protected $skipIoMappings;

    public function __construct(string $deploymentId, bool $cascade, bool $skipCustomListeners, bool $skipIoMappings)
    {
        $this->deploymentId = $deploymentId;
        $this->cascade = $cascade;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMappings = $skipIoMappings;
    }

    public function serialize()
    {
        return json_encode([
            'deploymentId' => $this->deploymentId,
            'cascade' => $this->cascade,
            'skipCustomListeners' => $this->skipCustomListeners,
            'skipIoMappings' => $this->skipIoMappings
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->deploymentId = $json->deploymentId;
        $this->cascade = $json->cascade;
        $this->skipCustomListeners = $json->skipCustomListeners;
        $this->skipIoMappings = $json->skipIoMappings;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("deploymentId", $this->deploymentId);

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
                $cmd->execute($cmd);
            });
        } finally {
            try {
                $commandContext->getTransactionContext()->addTransactionListener(TransactionState::ROLLED_BACK, $listener);
            } catch (\Exception $e) {
                //TX_LOG.debugTransactionOperation("Could not register transaction synchronization. Probably the TX has already been rolled back by application code.");
                $listener->execute($commandContext);
            }
        }

        return null;
    }
}

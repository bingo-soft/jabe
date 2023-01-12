<?php

namespace Jabe\Impl\Cfg\Standalone;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\{
    TransactionContextInterface,
    TransactionListenerInterface,
    //@TODO
    //TransactionLogger
    TransactionState
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\PersistenceSessionInterface;
use Jabe\Impl\Interceptor\CommandContext;

class StandaloneTransactionContext implements TransactionContextInterface
{
    //private final static TransactionLogger LOG = ProcessEngineLogger.TX_LOGGER;
    protected $commandContext;
    protected $stateTransactionListeners = [];
    private $lastTransactionState;

    public function __construct(CommandContext $commandContext)
    {
        $this->commandContext = $commandContext;
    }

    public function addTransactionListener(?string $transactionState, TransactionListenerInterface $transactionListener): void
    {
        $transactionListeners = null;
        if (array_key_exists($transactionState, $this->stateTransactionListeners)) {
            $transactionListeners = &$this->stateTransactionListeners[$transactionState];
        }
        if ($transactionListeners === null) {
            $this->stateTransactionListeners[$transactionState] = [];
            $transactionListeners = &$this->stateTransactionListeners[$transactionState];
        }
        $transactionListeners[] = $transactionListener;
    }

    public function commit(): void
    {
        //LOG.debugTransactionOperation("firing event committing...");

        $this->fireTransactionEvent(TransactionState::COMMITTING);

        //LOG.debugTransactionOperation("committing the persistence session...");

        $this->getPersistenceProvider()->commit();

        //LOG.debugTransactionOperation("firing event committed...");

        $this->fireTransactionEvent(TransactionState::COMMITTED);
    }

    protected function fireTransactionEvent(?string $transactionState): void
    {
        $this->setLastTransactionState($transactionState);
        if (empty($this->stateTransactionListeners)) {
            return;
        }
        $transactionListeners = null;
        if (array_key_exists($transactionState, $this->stateTransactionListeners)) {
            $transactionListeners = &$this->stateTransactionListeners[$transactionState];
        }
        if ($transactionListeners === null) {
            return;
        }
        foreach ($transactionListeners as $transactionListener) {
            $transactionListener->execute($commandContext);
        }
    }

    protected function setLastTransactionState(?string $transactionState): void
    {
        $this->lastTransactionState = $transactionState;
    }

    private function getPersistenceProvider(): PersistenceSessionInterface
    {
        return $this->commandContext->getSession(PersistenceSessionInterface::class);
    }

    public function rollback(): void
    {
        try {
            try {
                //LOG.debugTransactionOperation("firing event rollback...");
                $this->fireTransactionEvent(TransactionState::ROLLINGBACK);
            } catch (\Throwable $exception) {
                //LOG.exceptionWhileFiringEvent(TransactionState.ROLLINGBACK, exception);
                Context::getCommandInvocationContext()->trySetThrowable($exception);
            } finally {
                //LOG.debugTransactionOperation("rolling back the persistence session...");
                $this->getPersistenceProvider()->rollback();
            }
        } catch (\Throwable $exception) {
            //LOG.exceptionWhileFiringEvent(TransactionState.ROLLINGBACK, exception);
            Context::getCommandInvocationContext()->trySetThrowable($exception);
        } finally {
            //LOG.debugFiringEventRolledBack();
            $this->fireTransactionEvent(TransactionState::ROLLED_BACK);
        }
    }

    public function isTransactionActive(): bool
    {
        return TransactionState::ROLLINGBACK != $this->lastTransactionState && TransactionState::ROLLED_BACK != $this->lastTransactionState;
    }
}

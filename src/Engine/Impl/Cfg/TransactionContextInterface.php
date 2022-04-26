<?php

namespace Jabe\Engine\Impl\Cfg;

interface TransactionContextInterface
{
    /**
     * Commit the current transaction.
     */
    public function commit(): void;

    /**
     * Rollback the current transaction.
     */
    public function rollback(): void;


    /**
     * Add a {@link TransactionListener} to the current transaction.
     *
     * @param transactionState the transaction state for which the {@link TransactionListener} should be added.
     * @param transactionListener the {@link TransactionListener} to add.
     */
    public function addTransactionListener(string $transactionState, TransactionListenerInterface $transactionListener): void;

    public function isTransactionActive(): bool;
}

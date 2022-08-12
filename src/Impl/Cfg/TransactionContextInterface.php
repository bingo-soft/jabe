<?php

namespace Jabe\Impl\Cfg;

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
     * Add a TransactionListener to the current transaction.
     *
     * @param transactionState the transaction state for which the TransactionListener should be added.
     * @param transactionListener the TransactionListener to add.
     */
    public function addTransactionListener(string $transactionState, TransactionListenerInterface $transactionListener): void;

    public function isTransactionActive(): bool;
}

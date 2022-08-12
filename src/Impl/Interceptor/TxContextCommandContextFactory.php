<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Impl\Cfg\TransactionContextFactoryInterface;

class TxContextCommandContextFactory extends CommandContextFactory
{
    protected $transactionContextFactory;

    public function __construct()
    {
    }

    public function createCommandContext(): CommandContext
    {
        return new CommandContext($this->processEngineConfiguration, $this->transactionContextFactory);
    }

    public function getTransactionContextFactory(): TransactionContextFactoryInterface
    {
        return $this->transactionContextFactory;
    }

    public function setTransactionContextFactory(TransactionContextFactoryInterface $transactionContextFactory): void
    {
        $this->transactionContextFactory = $transactionContextFactory;
    }
}

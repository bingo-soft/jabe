<?php

namespace Jabe\Impl\Interceptor;

use Doctrine\DBAL\Connection;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\CommandLogger;

class TransactionInterceptor extends CommandInterceptor
{
    //protected final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $transactionManager;
    protected $requiresNew;
    protected $processEngineConfiguration;

    public function __construct(
        Connection $transactionManager,
        bool $requiresNew,
        ProcessEngineConfigurationImpl $processEngineConfiguration
    ) {
        $this->transactionManager = $transactionManager;
        $this->requiresNew = $requiresNew;
        $this->processEngineConfiguration = $processEngineConfiguration;
    }

    public function execute(CommandInterface $command, ...$args)
    {
        $existing = $this->isExisting();
        $isNew = !$existing || $this->requiresNew;
        if ($isNew) {
            $this->doBegin();
        }
        $result = null;
        try {
            if (empty($args) && !empty($this->getState())) {
                $args = $this->getState();
            }
            $result = $this->next->execute($command, ...$args);
        } catch (\Throwable $ex) {
            $this->doRollback();
            throw $ex;
        }
        if ($isNew) {
            $this->doCommit();
        }
        return $result;
    }

    private function doBegin(): void
    {
        try {
            $this->transactionManager->beginTransaction();
        } catch (\Throwable $e) {
            throw new TransactionException("Unable to begin transaction");
        }
    }

    private function isExisting(): bool
    {
        return $this->transactionManager->isTransactionActive();
    }

    private function doCommit(): void
    {
        try {
            $this->transactionManager->commit();
        } catch (\Throwable $ex) {
            $this->doRollback();
            throw $ex;
        }
    }

    private function doRollback(): void
    {
        try {
            $this->transactionManager->rollback();
        } catch (\Throwable $ex) {
            throw $ex;
        }
    }
}

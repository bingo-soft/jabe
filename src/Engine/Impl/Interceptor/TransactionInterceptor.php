<?php

namespace BpmPlatform\Engine\Impl\Interceptor;

use Doctrine\DBAL\Connection;
use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use BpmPlatform\Engine\Impl\Cmd\CommandLogger;

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

    public function execute(CommandInterface $command)
    {
        $existing = $this->isExisting();
        $isNew = !$existing || $this->requiresNew;
        if ($isNew) {
            $this->doBegin();
        }
        $result = null;
        try {
            $result = $this->next->execute($command);
        } catch (\Exception $ex) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $ex) {
            $this->doRollback();
            throw $ex;
        }
    }

    private function doRollback(): void
    {
        try {
            $this->transactionManager->rollback();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}

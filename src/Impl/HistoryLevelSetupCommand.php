<?php

namespace Jabe\Impl;

use Jabe\{
    ProcessEngineConfiguration,
    ProcessEngineException
};
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\DetermineHistoryLevelCmd;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\EnginePersistenceLogger;
use Jabe\Impl\Db\EntityManager\DbEntityManager;
use Jabe\Impl\History\HistoryLevel;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface
};
use Jabe\Impl\Persistence\Entity\{
    PropertyEntity,
    PropertyManager
};

class HistoryLevelSetupCommand implements CommandInterface
{
    //private final static EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    public function execute(CommandContext $commandContext, ...$args)
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        $this->checkStartupLockExists($commandContext);

        $databaseHistoryLevel = (new DetermineHistoryLevelCmd($processEngineConfiguration->getHistoryLevels()))->execute($commandContext);
        $this->determineAutoHistoryLevel($processEngineConfiguration, $databaseHistoryLevel);

        $configuredHistoryLevel = $processEngineConfiguration->getHistoryLevel();

        if ($databaseHistoryLevel === null) {
            $this->acquireExclusiveLock($commandContext);
            $databaseHistoryLevel = (new DetermineHistoryLevelCmd($processEngineConfiguration->getHistoryLevels()))->execute($commandContext);
            if ($databaseHistoryLevel === null) {
                //LOG.noHistoryLevelPropertyFound();
                $this->dbCreateHistoryLevel($commandContext);
            }
        } elseif ($configuredHistoryLevel->getId() != $databaseHistoryLevel->getId()) {
            throw new ProcessEngineException(
                "historyLevel mismatch: configuration says " . $configuredHistoryLevel
                . " and database says " . $databaseHistoryLevel
            );
        }

        return null;
    }

    public static function dbCreateHistoryLevel(CommandContext $commandContext): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $configuredHistoryLevel = $processEngineConfiguration->getHistoryLevel();
        $property = new PropertyEntity("historyLevel", $configuredHistoryLevel->getId());
        $commandContext->getSession(DbEntityManager::class)->insert($property);
        //LOG.creatingHistoryLevelPropertyInDatabase(configuredHistoryLevel);
    }

    /**
     *
     * @return Integer value representing the history level or <code>null</code> if none found
     */
    public static function databaseHistoryLevel(CommandContext $commandContext): ?int
    {
        try {
            $historyLevelProperty =  $commandContext->getPropertyManager()->findPropertyById("historyLevel");
            return $historyLevelProperty !== null ? intval($historyLevelProperty->getValue()) : null;
        } catch (\Throwable $e) {
            //LOG.couldNotSelectHistoryLevel(e.getMessage());
            return null;
        }
    }

    protected function determineAutoHistoryLevel(ProcessEngineConfigurationImpl $engineConfiguration, HistoryLevel $databaseHistoryLevel = null): void
    {
        $configuredHistoryLevel = $engineConfiguration->getHistoryLevel();

        if ($configuredHistoryLevel === null && ProcessEngineConfiguration::HISTORY_AUTO == $engineConfiguration->getHistory()) {
            // automatically determine history level or use default AUDIT
            if ($databaseHistoryLevel !== null) {
                $engineConfiguration->setHistoryLevel($databaseHistoryLevel);
            } else {
                $engineConfiguration->setHistoryLevel($engineConfiguration->getDefaultHistoryLevel());
            }
        }
    }

    protected function checkStartupLockExists(CommandContext $commandContext): void
    {
        $historyStartupProperty = $commandContext->getPropertyManager()->findPropertyById("startup.lock");
        if ($historyStartupProperty === null) {
            //LOG.noStartupLockPropertyFound();
        }
    }

    protected function acquireExclusiveLock(CommandContext $commandContext): void
    {
        $propertyManager = $commandContext->getPropertyManager();
        //exclusive lock
        $propertyManager->acquireExclusiveLockForStartup();
    }

    public function isRetryable(): bool
    {
        return true;
    }
}

<?php

namespace Jabe\Engine\Impl\Db;

use Jabe\Engine\ProcessEngineInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\EntityManager\Operation\{
    DbBulkOperation,
    DbEntityOperation,
    DbOperation,
    DbOperationType
};
use Jabe\Engine\Impl\History\HistoryLevel;

abstract class AbstractPersistenceSession implements PersistenceSessionInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    protected $listeners = [];

    public function executeDbOperation(DbOperation $operation): void
    {
        switch ($operation->getOperationType()) {
            case DbOperationType::INSERT:
                $this->insertEntity($operation);
                break;
            case DbOperationType::DELETE:
                $this->deleteEntity($operation);
                break;
            case DbOperationType::DELETE_BULK:
                $this->deleteBulk($operation);
                break;
            case DbOperationType::UPDATE:
                $this->updateEntity($operation);
                break;
            case DbOperationType::UPDATE_BULK:
                $this->updateBulk($operation);
                break;
        }
    }

    abstract protected function insertEntity(DbEntityOperation $operation): void;

    abstract protected function deleteEntity(DbEntityOperation $operation): void;

    abstract protected function deleteBulk(DbBulkOperation $operation): void;

    abstract protected function updateEntity(DbEntityOperation $operation): void;

    abstract protected function updateBulk(DbBulkOperation $operation): void;

    abstract protected function getDbVersion(): string;

    public function dbSchemaCreate(): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        $configuredHistoryLevel = $processEngineConfiguration->getHistoryLevel();
        if (
            (!$processEngineConfiguration->isDbHistoryUsed())
            && ($configuredHistoryLevel != HistoryLevel::historyLevelNone())
        ) {
            //throw LOG.databaseHistoryLevelException(configuredHistoryLevel.getName());
            throw new \Exception("databaseHistoryLevelException");
        }

        if ($this->isEngineTablePresent()) {
            $dbVersion = $this->getDbVersion();
            if (ProcessEngineInterface::VERSION != $dbVersion) {
                //throw LOG.wrongDbVersionException(ProcessEngine.VERSION, dbVersion);
                throw new \Exception("wrongDbVersionException");
            }
        } else {
            $this->dbSchemaCreateEngine();
        }

        if ($processEngineConfiguration->isDbHistoryUsed()) {
            $this->dbSchemaCreateHistory();
        }

        if ($processEngineConfiguration->isDbIdentityUsed()) {
            $this->dbSchemaCreateIdentity();
        }

        /*if (processEngineConfiguration.isCmmnEnabled()) {
            dbSchemaCreateCmmn();
        }

        if (processEngineConfiguration.isCmmnEnabled() && processEngineConfiguration.isDbHistoryUsed()) {
            dbSchemaCreateCmmnHistory();
        }

        if (processEngineConfiguration.isDmnEnabled()) {
            dbSchemaCreateDmn();
            if (processEngineConfiguration.isDbHistoryUsed()) {
                dbSchemaCreateDmnHistory();
            }
        }*/
    }

    abstract protected function dbSchemaCreateIdentity(): void;

    abstract protected function dbSchemaCreateHistory(): void;

    abstract protected function dbSchemaCreateEngine(): void;

    /*abstract protected function dbSchemaCreateCmmn(): void;

    abstract protected function dbSchemaCreateCmmnHistory(): void;

    abstract protected function void dbSchemaCreateDmn();

    abstract protected function void dbSchemaCreateDmnHistory();*/

    public function dbSchemaDrop(): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        /*if ($processEngineConfiguration->isDmnEnabled()) {
            dbSchemaDropDmn();
            if (processEngineConfiguration.isDbHistoryUsed()) {
                dbSchemaDropDmnHistory();
            }
        }

        if (processEngineConfiguration.isCmmnEnabled()) {
            dbSchemaDropCmmn();
        }

        dbSchemaDropEngine();

        if (processEngineConfiguration.isCmmnEnabled() && processEngineConfiguration.isDbHistoryUsed()) {
            dbSchemaDropCmmnHistory();
        }*/

        if ($processEngineConfiguration->isDbHistoryUsed()) {
            $this->dbSchemaDropHistory();
        }

        if ($processEngineConfiguration->isDbIdentityUsed()) {
            $this->dbSchemaDropIdentity();
        }
    }

    abstract protected function dbSchemaDropIdentity(): void;

    abstract protected function dbSchemaDropHistory(): void;

    abstract protected function dbSchemaDropEngine(): void;

    /*abstract protected function void dbSchemaDropCmmn();

    abstract protected function void dbSchemaDropCmmnHistory();

    abstract protected function void dbSchemaDropDmn();

    abstract protected function void dbSchemaDropDmnHistory();*/

    public function dbSchemaPrune(): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        if ($this->isHistoryTablePresent() && !$processEngineConfiguration->isDbHistoryUsed()) {
            $this->dbSchemaDropHistory();
        }
        if ($this->isIdentityTablePresent() && !$processEngineConfiguration->isDbIdentityUsed()) {
            $this->dbSchemaDropIdentity();
        }
        /*if (isCmmnTablePresent() && !processEngineConfiguration.isCmmnEnabled()) {
            dbSchemaDropCmmn();
        }
        if (isCmmnHistoryTablePresent() && (!processEngineConfiguration.isCmmnEnabled() || !processEngineConfiguration.isDbHistoryUsed())) {
            dbSchemaDropCmmnHistory();
        }
        if (isDmnTablePresent() && !processEngineConfiguration.isDmnEnabled()) {
            dbSchemaDropDmn();
        }
        if (isDmnHistoryTablePresent() && (!processEngineConfiguration.isDmnEnabled() || !processEngineConfiguration.isDbHistoryUsed())) {
            dbSchemaDropDmnHistory();
        }*/
    }

    abstract public function isEngineTablePresent(): bool;

    abstract public function isHistoryTablePresent(): bool;

    abstract public function isIdentityTablePresent(): bool;

    /*abstract public function boolean isCmmnTablePresent();

    abstract public function boolean isCmmnHistoryTablePresent();

    abstract public function boolean isDmnTablePresent();

    abstract public function boolean isDmnHistoryTablePresent();*/

    public function dbSchemaUpdate(): void
    {
        /*$processEngineConfiguration = Context::getProcessEngineConfiguration();
        if (!$this->isEngineTablePresent()) {
            $this->dbSchemaCreateEngine();
        }

        if (!$this->isHistoryTablePresent() && $processEngineConfiguration->isDbHistoryUsed()) {
            $this->dbSchemaCreateHistory();
        }

        if (!$this->isIdentityTablePresent() && $processEngineConfiguration->isDbIdentityUsed()) {
            $this->dbSchemaCreateIdentity();
        }*/

        /*if (!isCmmnTablePresent() && processEngineConfiguration.isCmmnEnabled()) {
            dbSchemaCreateCmmn();
        }

        if (!isCmmnHistoryTablePresent() && processEngineConfiguration.isCmmnEnabled() && processEngineConfiguration.isDbHistoryUsed()) {
            dbSchemaCreateCmmnHistory();
        }

        if (!isDmnTablePresent() && processEngineConfiguration.isDmnEnabled()) {
            dbSchemaCreateDmn();
        }

        if (!isDmnHistoryTablePresent() && processEngineConfiguration.isDmnEnabled() && processEngineConfiguration.isDbHistoryUsed()) {
            dbSchemaCreateDmnHistory();
        }*/
    }

    public function getTableNamesPresent(): array
    {
        return [];
    }

    public function addEntityLoadListener(EntityLoadListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    protected function fireEntityLoaded($result = null): void
    {
        if ($result !== null && $result instanceof DbEntityInterface) {
            $entity = $result;
            foreach ($this->listeners as $entityLoadListener) {
                $entityLoadListener->onEntityLoaded($entity);
            }
        }
    }
}

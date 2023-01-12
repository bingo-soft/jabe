<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineBootstrapCommandInterface;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger
};
use Jabe\Impl\Db\EntityManager\{
    OptimisticLockingListenerInterface,
    OptimisticLockingResult
};
use Jabe\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\{
    EverLivingJobEntity,
    PropertyEntity,
    PropertyManager
};
use Ramsey\Uuid\Uuid;

class BootstrapEngineCommand implements ProcessEngineBootstrapCommandInterface
{
    //private final static EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    //protected static final String TELEMETRY_PROPERTY_NAME = "camunda.telemetry.enabled";
    protected const INSTALLATION_PROPERTY_NAME = "jabe.installation.id";

    public function execute(CommandContext $commandContext)
    {
        $this->initializeInstallationId($commandContext);

        $this->checkDeploymentLockExists($commandContext);

        if ($this->isHistoryCleanupEnabled($commandContext)) {
            $this->checkHistoryCleanupLockExists($commandContext);
            $this->createHistoryCleanupJob($commandContext);
        }

        //$this->initializeTelemetryProperty($commandContext);
        // installationId needs to be updated in the telemetry data
        //$this->updateTelemetryData($commandContext);
        //$this->startTelemetryReporter($commandContext);

        return null;
    }

    protected function createHistoryCleanupJob(CommandContext $commandContext): void
    {
        if (Context::getProcessEngineConfiguration()->getManagementService()->getTableMetaData("ACT_RU_JOB") !== null) {
            // CAM-9671: avoid transaction rollback due to the OLE being caught in CommandContext#close
            $commandContext->getDbEntityManager()->registerOptimisticLockingListener(new class () implements OptimisticLockingListenerInterface {

                public function getEntityType(): ?string
                {
                    return EverLivingJobEntityInterface::class;
                }

                public function failedOperation(DbOperation $operation): ?string
                {

                    // nothing to do, reconfiguration will be handled later on
                    return OptimisticLockingResult::IGNORE;
                }
            });
            Context::getProcessEngineConfiguration()->getHistoryService()->cleanUpHistoryAsync();
        }
    }

    public function checkDeploymentLockExists(CommandContext $commandContext): void
    {
        $deploymentLockProperty = $commandContext->getPropertyManager()->findPropertyById("deployment.lock");
        if ($deploymentLockProperty === null) {
            //LOG.noDeploymentLockPropertyFound();
        }
    }

    public function checkHistoryCleanupLockExists(CommandContext $commandContext): void
    {
        $historyCleanupLockProperty = $commandContext->getPropertyManager()->findPropertyById("history.cleanup.job.lock");
        if ($historyCleanupLockProperty === null) {
            //LOG.noHistoryCleanupLockPropertyFound();
        }
    }

    protected function isHistoryCleanupEnabled(CommandContext $commandContext): bool
    {
        return $commandContext->getProcessEngineConfiguration()
            ->isHistoryCleanupEnabled();
    }

    public function initializeInstallationId(CommandContext $commandContext): void
    {
        $this->checkInstallationIdLockExists($commandContext);

        $databaseInstallationId = $this->databaseInstallationId($commandContext);

        if (empty($databaseInstallationId)) {
            $this->acquireExclusiveInstallationIdLock($commandContext);
            $databaseInstallationId = $this->databaseInstallationId($commandContext);

            if (empty($databaseInstallationId)) {
                //LOG.noInstallationIdPropertyFound();
                $this->createInstallationProperty($commandContext);
            }
        } else {
            //LOG.installationIdPropertyFound(databaseInstallationId);
            $commandContext->getProcessEngineConfiguration()->setInstallationId($databaseInstallationId);
        }
    }

    protected function createInstallationProperty(CommandContext $commandContext): void
    {
        $installationId = Uuid::uuid1();
        $property = new PropertyEntity(self::INSTALLATION_PROPERTY_NAME, installationId);
        $commandContext->getPropertyManager()->insert($property);
        //LOG.creatingInstallationPropertyInDatabase(property.getValue());
        $commandContext->getProcessEngineConfiguration()->setInstallationId($installationId);
    }

    protected function databaseInstallationId(CommandContext $commandContext): ?string
    {
        try {
            $installationIdProperty = $commandContext->getPropertyManager()->findPropertyById(self::INSTALLATION_PROPERTY_NAME);
            return $installationIdProperty !== null ? $installationIdProperty->getValue() : null;
        } catch (\Exception $e) {
            //LOG.couldNotSelectInstallationId(e.getMessage());
            return null;
        }
    }

    protected function checkInstallationIdLockExists(CommandContext $commandContext): void
    {
        $installationIdProperty = $commandContext->getPropertyManager()->findPropertyById("installationId.lock");
        if ($installationIdProperty === null) {
            //LOG.noInstallationIdLockPropertyFound();
        }
    }

    protected function acquireExclusiveInstallationIdLock(CommandContext $commandContext): void
    {
        $propertyManager = $commandContext->getPropertyManager();
        //exclusive lock
        $propertyManager->acquireExclusiveLockForInstallationId();
    }

    public function isRetryable(): bool
    {
        return true;
    }
}

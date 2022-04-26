<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Persistence\AbstractManager;

class PropertyManager extends AbstractManager
{
    public function findPropertyById(string $propertyId): ?PropertyEntity
    {
        return $this->getDbEntityManager()->selectById(PropertyEntity::class, $propertyId);
    }

    public function acquireExclusiveLock(): void
    {
        // We lock a special deployment lock property
        $this->getDbEntityManager()->lock("lockDeploymentLockProperty");
    }

    public function acquireExclusiveLockForHistoryCleanupJob(): void
    {
        // We lock a special history cleanup lock property
        $this->getDbEntityManager()->lock("lockHistoryCleanupJobLockProperty");
    }

    public function acquireExclusiveLockForStartup(): void
    {
        // We lock a special startup lock property
        $this->getDbEntityManager()->lock("lockStartupLockProperty");
    }

    public function acquireExclusiveLockForTelemetry(): void
    {
        // We lock a special telemetry lock property
        $this->getDbEntityManager()->lock("lockTelemetryLockProperty");
    }

    public function acquireExclusiveLockForInstallationId(): void
    {
        // We lock a special installation id lock property
        $this->getDbEntityManager()->lock("lockInstallationIdLockProperty");
    }
}

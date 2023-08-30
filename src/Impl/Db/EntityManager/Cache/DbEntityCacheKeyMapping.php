<?php

namespace Jabe\Impl\Db\EntityManager\Cache;

use Jabe\Impl\History\Event\{
    HistoricDetailEventEntity,
    HistoricFormPropertyEventEntity,
    HistoricVariableUpdateEventEntity
};
use Jabe\Impl\Persistence\Entity\{
    AcquirableJobEntity,
    HistoricDetailVariableInstanceUpdateEntity,
    HistoricFormPropertyEntity,
    JobEntity,
    MessageEntity,
    TimerEntity
};

class DbEntityCacheKeyMapping
{
    protected $entityCacheKeys;

    public function __construct()
    {
        $this->entityCacheKeys = [];
    }

    public function getEntityCacheKey(?string $entityType): ?string
    {
        if (array_key_exists($entityType, $this->entityCacheKeys)) {
            return $this->entityCacheKeys[$entityType];
        }
        return $entityType;
    }

    public function registerEntityCacheKey(?string $entityType, ?string $cacheKey): void
    {
        $this->entityCacheKeys[$entityType] = $cacheKey;
    }

    public static function defaultEntityCacheKeyMapping(): DbEntityCacheKeyMapping
    {
        $mapping = new DbEntityCacheKeyMapping();

        $mapping->registerEntityCacheKey(JobEntity::class, AcquirableJobEntity::class);
        // subclasses of JobEntity
        $mapping->registerEntityCacheKey(MessageEntity::class, AcquirableJobEntity::class);
        $mapping->registerEntityCacheKey(TimerEntity::class, AcquirableJobEntity::class);

        // subclasses of HistoricDetailEventEntity
        $mapping->registerEntityCacheKey(HistoricFormPropertyEntity::class, HistoricDetailEventEntity::class);
        $mapping->registerEntityCacheKey(HistoricFormPropertyEventEntity::class, HistoricDetailEventEntity::class);
        $mapping->registerEntityCacheKey(HistoricVariableUpdateEventEntity::class, HistoricDetailEventEntity::class);
        $mapping->registerEntityCacheKey(HistoricDetailVariableInstanceUpdateEntity::class, HistoricDetailEventEntity::class);

        return $mapping;
    }

    public static function emptyMapping(): DbEntityCacheKeyMapping
    {
        return new DbEntityCacheKeyMapping();
    }
}

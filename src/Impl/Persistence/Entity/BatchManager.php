<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\Page;
use Jabe\Impl\Batch\{
    BatchEntity,
    BatchQueryImpl
};
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Persistence\AbstractManager;

class BatchManager extends AbstractManager
{
    public function insertBatch(BatchEntity $batch): void
    {
        $batch->setCreateUserId($this->getCommandContext()->getAuthenticatedUserId());
        $this->getDbEntityManager()->insert($batch);
    }

    public function findBatchById(?string $id): BatchEntity
    {
        return $this->getDbEntityManager()->selectById(BatchEntity::class, $id);
    }

    public function findBatchCountByQueryCriteria(BatchQueryImpl $batchQuery): int
    {
        $this->configureQuery($batchQuery);
        return $this->getDbEntityManager()->selectOne("selectBatchCountByQueryCriteria", $batchQuery);
    }

    public function findBatchesByQueryCriteria(BatchQueryImpl $batchQuery, ?Page $page): array
    {
        $this->configureQuery($batchQuery);
        return $this->getDbEntityManager()->selectList("selectBatchesByQueryCriteria", $batchQuery, $page);
    }

    public function updateBatchSuspensionStateById(?string $batchId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["batchId"] = $batchId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();

        $queryParameter = new ListQueryParameterObject();
        $queryParameter->setParameter($parameters);

        $this->getDbEntityManager()->update(BatchEntity::class, "updateBatchSuspensionStateByParameters", $queryParameter);
    }

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        $this->getAuthorizationManager()->configureBatchQuery($batchQuery);
        $this->getTenantManager()->configureQuery($batchQuery);
    }
}

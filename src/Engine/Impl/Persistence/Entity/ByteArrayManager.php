<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Db\ListQueryParameterObject;
use Jabe\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Engine\Impl\Persistence\AbstractManager;
use Jabe\Engine\Impl\Util\ClockUtil;

class ByteArrayManager extends AbstractManager
{
    /**
     * Deletes the ByteArrayEntity with the given id from the database.
     * Important: this operation will NOT do any optimistic locking, to avoid loading the
     * bytes in memory. So use this method only in conjunction with an entity that has
     * optimistic locking!.
     */
    public function deleteByteArrayById(string $byteArrayEntityId): void
    {
        $this->getDbEntityManager()->delete(ByteArrayEntity::class, "deleteByteArrayNoRevisionCheck", $byteArrayEntityId);
    }

    public function insertByteArray(ByteArrayEntity $arr): void
    {
        $arr->setCreateTime(ClockUtil::getCurrentTime()->format('c'));
        $this->getDbEntityManager()->insert($arr);
    }

    public function addRemovalTimeToByteArraysByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(ByteArrayEntity::class, "updateByteArraysByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToByteArraysByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        // Make individual statements for each entity type that references byte arrays.
        // This can lead to query plans that involve less aggressive locking by databases (e.g. DB2).
        // See CAM-10360 for reference.
        $this->getDbEntityManager()
            ->updatePreserveOrder(ByteArrayEntity::class, "updateVariableByteArraysByProcessInstanceId", $parameters);
        $this->getDbEntityManager()
            ->updatePreserveOrder(ByteArrayEntity::class, "updateDecisionInputsByteArraysByProcessInstanceId", $parameters);
        $this->getDbEntityManager()
            ->updatePreserveOrder(ByteArrayEntity::class, "updateDecisionOutputsByteArraysByProcessInstanceId", $parameters);
        $this->getDbEntityManager()
            ->updatePreserveOrder(ByteArrayEntity::class, "updateJobLogByteArraysByProcessInstanceId", $parameters);
        $this->getDbEntityManager()
            ->updatePreserveOrder(ByteArrayEntity::class, "updateExternalTaskLogByteArraysByProcessInstanceId", $parameters);
        $this->getDbEntityManager()
            ->updatePreserveOrder(ByteArrayEntity::class, "updateAttachmentByteArraysByProcessInstanceId", $parameters);
    }

    public function deleteByteArraysByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
    {
        $parameters = [];
        $parameters["removalTime"] = $removalTime;
        if ($minuteTo - $minuteFrom + 1 < 60) {
            $parameters["minuteFrom"] = $minuteFrom;
            $parameters["minuteTo"] = $minuteTo;
        }
        $parameters["batchSize"] = $batchSize;

        return $this->getDbEntityManager()
            ->deletePreserveOrder(
                ByteArrayEntity::class,
                "deleteByteArraysByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }
}

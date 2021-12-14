<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\{
    Direction,
    QueryOrderingProperty,
    QueryPropertyImpl
};
use BpmPlatform\Engine\Impl\Db\{
    DbEntityInterface,
    ListQueryParameterObject
};
use BpmPlatform\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use BpmPlatform\Engine\Impl\Persistence\AbstractHistoricManager;
use BpmPlatform\Engine\Task\{
    CommentInterface,
    EventInterface
};

class CommentManager extends AbstractHistoricManager
{
    public function delete(DbEntityInterface $dbEntity): void
    {
        $this->checkHistoryEnabled();
        parent::delete($dbEntity);
    }

    public function insert(DbEntityInterface $dbEntity): void
    {
        $this->checkHistoryEnabled();
        parent::insert($dbEntity);
    }

    public function findCommentsByTaskId(string $taskId): array
    {
        $this->checkHistoryEnabled();
        return $this->getDbEntityManager()->selectList("selectCommentsByTaskId", $taskId);
    }

    public function findEventsByTaskId(string $taskId): array
    {
        $this->checkHistoryEnabled();

        $query = new ListQueryParameterObject();
        $query->setParameter($taskId);
        $query->addOrderingProperty(new QueryOrderingProperty(new QueryPropertyImpl("TIME_"), Direction::DESCENDING));

        return $this->getDbEntityManager()->selectList("selectEventsByTaskId", $query);
    }

    public function deleteCommentsByTaskId(string $taskId): void
    {
        $this->checkHistoryEnabled();
        $this->getDbEntityManager()->delete(CommentEntity::class, "deleteCommentsByTaskId", $taskId);
    }

    public function deleteCommentsByProcessInstanceIds(array $processInstanceIds): void
    {
        $parameters = [];
        $parameters["processInstanceIds"] = $processInstanceIds;
        $this->deleteComments($parameters);
    }

    public function deleteCommentsByTaskProcessInstanceIds(array $processInstanceIds): void
    {
        $parameters = [];
        $parameters["taskProcessInstanceIds"] = $processInstanceIds;
        $this->deleteComments($parameters);
    }

    public function deleteCommentsByTaskCaseInstanceIds(array $caseInstanceIds): void
    {
        $parameters = [];
        $parameters["taskCaseInstanceIds"] = $caseInstanceIds;
        $this->deleteComments($parameters);
    }

    protected function deleteComments(array $parameters): void
    {
        $this->getDbEntityManager()->deletePreserveOrder(CommentEntity::class, "deleteCommentsByIds", $parameters);
    }

    public function findCommentsByProcessInstanceId(string $processInstanceId): array
    {
        $this->checkHistoryEnabled();
        return $this->getDbEntityManager()->selectList("selectCommentsByProcessInstanceId", $processInstanceId);
    }

    public function findCommentByTaskIdAndCommentId(string $taskId, string $commentId): ?CommentEntity
    {
        $this->checkHistoryEnabled();

        $parameters = [];
        $parameters["taskId"] = $taskId;
        $parameters["id"] = $commentId;

        return $this->getDbEntityManager()->selectOne("selectCommentByTaskIdAndCommentId", $parameters);
    }

    public function addRemovalTimeToCommentsByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(CommentEntity::class, "updateCommentsByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToCommentsByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(CommentEntity::class, "updateCommentsByProcessInstanceId", $parameters);
    }

    public function deleteCommentsByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
                CommentEntity::class,
                "deleteCommentsByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }
}

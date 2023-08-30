<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Impl\Persistence\AbstractHistoricManager;
use Jabe\Task\AttachmentInterface;

class AttachmentManager extends AbstractHistoricManager
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function findAttachmentsByProcessInstanceId(?string $processInstanceId): array
    {
        $this->checkHistoryEnabled();
        return $this->getDbEntityManager()->selectList("selectAttachmentsByProcessInstanceId", $processInstanceId);
    }

    public function findAttachmentsByTaskId(?string $taskId): array
    {
        $this->checkHistoryEnabled();
        return $this->getDbEntityManager()->selectList("selectAttachmentsByTaskId", $taskId);
    }

    public function addRemovalTimeToAttachmentsByRootProcessInstanceId(?string $rootProcessInstanceId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(AttachmentEntity::class, "updateAttachmentsByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToAttachmentsByProcessInstanceId(?string $processInstanceId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(AttachmentEntity::class, "updateAttachmentsByProcessInstanceId", $parameters);
    }

    public function deleteAttachmentsByTaskId(?string $taskId): void
    {
        $this->checkHistoryEnabled();
        $attachments = $this->getDbEntityManager()->selectList("selectAttachmentsByTaskId", $taskId);
        foreach ($attachments as $attachment) {
            $contentId = $attachment->getContentId();
            if ($contentId !== null) {
                $this->getByteArrayManager()->deleteByteArrayById($contentId);
            }
            $this->getDbEntityManager()->delete($attachment);
        }
    }

    public function deleteAttachmentsByProcessInstanceIds(array $processInstanceIds): void
    {
        $parameters = [];
        $parameters["processInstanceIds"] = $processInstanceIds;
        $this->deleteAttachments($parameters);
    }

    public function deleteAttachmentsByTaskProcessInstanceIds(array $processInstanceIds): void
    {
        $parameters = [];
        $parameters["taskProcessInstanceIds"] = $processInstanceIds;
        $this->deleteAttachments($parameters);
    }

    public function deleteAttachmentsByTaskCaseInstanceIds(array $caseInstanceIds): void
    {
        $parameters = [];
        $parameters["caseInstanceIds"] = $caseInstanceIds;
        $this->deleteAttachments($parameters);
    }

    protected function deleteAttachments(array $parameters): void
    {
        $this->getDbEntityManager()->deletePreserveOrder(ByteArrayEntity::class, "deleteAttachmentByteArraysByIds", $parameters);
        $this->getDbEntityManager()->deletePreserveOrder(AttachmentEntity::class, "deleteAttachmentByIds", $parameters);
    }

    public function findAttachmentByTaskIdAndAttachmentId(?string $taskId, ?string $attachmentId): ?AttachmentInterface
    {
        $this->checkHistoryEnabled();

        $parameters = [];
        $parameters["taskId"] = $taskId;
        $parameters["id"] = $attachmentId;

        return $this->getDbEntityManager()->selectOne("selectAttachmentByTaskIdAndAttachmentId", $parameters);
    }

    public function deleteAttachmentsByRemovalTime(?string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): ?DbOperation
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
                AttachmentEntity::class,
                "deleteAttachmentsByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }
}

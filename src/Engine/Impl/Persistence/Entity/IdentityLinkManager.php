<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Persistence\AbstractManager;

class IdentityLinkManager extends AbstractManager
{
    public function findIdentityLinksByTaskId(string $taskId): array
    {
        return $this->getDbEntityManager()->selectList("selectIdentityLinksByTask", $taskId);
    }

    public function findIdentityLinksByProcessDefinitionId(string $processDefinitionId): array
    {
        return $this->getDbEntityManager()->selectList("selectIdentityLinksByProcessDefinition", $processDefinitionId);
    }

    public function findIdentityLinkByTaskUserGroupAndType(string $taskId, string $userId, string $groupId, string $type): array
    {
        $parameters = [];
        $parameters["taskId"] = $taskId;
        $parameters["userId"] = $userId;
        $parameters["groupId"] = $groupId;
        $parameters["type"] = $type;
        return $this->getDbEntityManager()->selectList("selectIdentityLinkByTaskUserGroupAndType", $parameters);
    }

    public function findIdentityLinkByProcessDefinitionUserAndGroup(string $processDefinitionId, string $userId, string $groupId): array
    {
        $parameters = [];
        $parameters["processDefinitionId"] = $processDefinitionId;
        $parameters["userId"] = $userId;
        $parameters["groupId"] = $groupId;
        return $this->getDbEntityManager()->selectList("selectIdentityLinkByProcessDefinitionUserAndGroup", $parameters);
    }

    public function deleteIdentityLinksByProcDef(string $processDefId): void
    {
        $this->getDbEntityManager()->delete(IdentityLinkEntity::class, "deleteIdentityLinkByProcDef", $processDefId);
    }
}

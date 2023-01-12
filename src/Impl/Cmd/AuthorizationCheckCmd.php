<?php

namespace Jabe\Impl\Cmd;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface,
    Resources
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Db\EnginePersistenceLogger;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class AuthorizationCheckCmd implements CommandInterface
{
    protected $userId;
    protected $groupIds = [];
    protected $permission;
    protected $resource;
    protected $resourceId;

    public function __construct(?string $userId, array $groupIds, PermissionInterface $permission, ResourceInterface $resource, ?string $resourceId)
    {
        $this->userId = $userId;
        $this->groupIds = $groupIds;
        $this->permission = $permission;
        $this->resource = $resource;
        $this->resourceId = $resourceId;
        $this->validate($userId, $groupIds, $permission, $resource);
    }

    public function execute(CommandContext $commandContext)
    {
        $authorizationManager = $commandContext->getAuthorizationManager();
        if ($authorizationManager->isPermissionDisabled($this->permission)) {
            //throw LOG.disabledPermissionException(permission.getName());
            throw new \Exception("disabledPermissionException " . $this->permission->getName());
        }

        if ($this->isHistoricInstancePermissionsDisabled($commandContext) && $this->isHistoricInstanceResource()) {
            //throw LOG.disabledHistoricInstancePermissionsException();
            throw new \Exception("disabledHistoricInstancePermissionsException ");
        }

        return $authorizationManager->isAuthorized($this->userId, $this->groupIds, $this->permission, $this->resource, $this->resourceId);
    }

    protected function validate(?string $userId, array $groupIds, PermissionInterface $permission, ResourceInterface $resource): void
    {
        EnsureUtil::ensureAtLeastOneNotNull("Authorization must have a 'userId' or/and a 'groupId'.", $userId, $groupIds);
        EnsureUtil::ensureNotNull("Invalid permission for an authorization", "authorization.getResource()", $permission);
        EnsureUtil::ensureNotNull("Invalid resource for an authorization", "authorization.getResource()", $resource);
    }

    protected function isHistoricInstancePermissionsDisabled(CommandContext $commandContext): bool
    {
        return !$commandContext->getProcessEngineConfiguration()->isEnableHistoricInstancePermissions();
    }

    protected function isHistoricInstanceResource(): bool
    {
        return Resources::historicTask() == $this->resource ||
               Resources::historicProcessInstance() == $this->resource;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}

<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\AuthorizationServiceInterface;
use Jabe\Engine\Authorization\{
    AuthorizationInterface,
    AuthorizationQueryInterface,
    PermissionInterface,
    ResourceInterface
};
use Jabe\Engine\Impl\Cmd\{
    AuthorizationCheckCmd,
    CreateAuthorizationCommand,
    DeleteAuthorizationCmd,
    SaveAuthorizationCmd
};

class AuthorizationServiceImpl extends ServiceImpl implements AuthorizationServiceInterface
{
    public function createAuthorizationQuery(): AuthorizationQueryInterface
    {
        return new AuthorizationQueryImpl($this->commandExecutor);
    }

    public function createNewAuthorization(int $type): AuthorizationInterface
    {
        return $this->commandExecutor->execute(new CreateAuthorizationCommand($type));
    }

    public function saveAuthorization(AuthorizationInterface $authorization): AuthorizationInterface
    {
        return $this->commandExecutor->execute(new SaveAuthorizationCmd($authorization));
    }

    public function deleteAuthorization(string $authorizationId): void
    {
        $this->commandExecutor->execute(new DeleteAuthorizationCmd($authorizationId));
    }

    public function isUserAuthorized(string $userId, array $groupIds, PermissionInterface $permission, ResourceInterface $resource, string $resourceId = null): bool
    {
        return $this->commandExecutor->execute(new AuthorizationCheckCmd($userId, $groupIds, $permission, $resource, $resourceId));
    }
}

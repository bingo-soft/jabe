<?php

namespace Jabe;

use Jabe\Authorization\{
    AuthorizationInterface,
    AuthorizationQueryInterface,
    PermissionInterface,
    ResourceInterface
};

interface AuthorizationServiceInterface
{
    public function createNewAuthorization(int $authorizationType): AuthorizationInterface;

    public function saveAuthorization(AuthorizationInterface $authorization): AuthorizationInterface;

    public function deleteAuthorization(?string $authorizationId): void;

    public function createAuthorizationQuery(): AuthorizationQueryInterface;

    public function isUserAuthorized(
        ?string $userId,
        array $groupIds,
        PermissionInterface $permission,
        ResourceInterface $resource
    ): bool;
}

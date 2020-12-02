<?php

namespace BpmPlatform\Engine\Authorization;

interface AuthorizationInterface
{
    public const AUTH_TYPE_GLOBAL = 0;
    public const AUTH_TYPE_GRANT = 1;
    public const AUTH_TYPE_REVOKE = 2;
    public const ANY = '*';

    public function addPermission(PermissionInterface $premission): void;

    public function removePermission(PermissionInterface $premission): void;

    public function isPermissionGranted(PermissionInterface $premission): bool;

    public function isPermissionRevoked(PermissionInterface $premission): bool;

    public function isEveryPermissionGranted(): bool;

    public function isEveryPermissionRevoked(): bool;

    public function getPermissions(): array;

    public function setPermissions(array $permissions): void;

    public function getId(): string;

    public function setResourceId(string $resourceId): void;

    public function getResourceId(): string;

    public function setUserId(string $userId): void;

    public function getUserId(): string;

    public function setGroupId(string $groupId): void;

    public function getGroupId(): string;

    public function getAuthorizationType(): int;

    public function getRemovalTime(): string;

    public function getRootProcessInstanceId(): string;
}

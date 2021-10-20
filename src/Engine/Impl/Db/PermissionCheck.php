<?php

namespace BpmPlatform\Engine\Impl\Db;

use BpmPlatform\Engine\Authorization\{
    PermissionInterface,
    ResourceInterface
};

class PermissionCheck
{
    /** the permission to check for */
    protected $permission;
    protected $perms;

    /** the type of the resource to check permissions for */
    protected $resource;
    protected $resourceType;

    /** the id of the resource to check permission for */
    protected $resourceId;

    /** query parameter for resource Id. Is injected as RAW parameter into the query */
    protected $resourceIdQueryParam;

    protected $authorizationNotFoundReturnValue = null;

    public function getPermission(): PermissionInterface
    {
        return $this->permission;
    }

    public function setPermission(PermissionInterface $permission): void
    {
        $this->permission = $permission;
        if ($permission != null) {
            $this->perms = $permission->getValue();
        }
    }

    public function getPerms(): int
    {
        return $this->perms;
    }

    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }

    public function setResource(ResourceInterface $resource): void
    {
        $this->resource = $resource;

        if ($resource != null) {
            $this->resourceType = $resource->resourceType();
        }
    }

    public function getResourceType(): int
    {
        return $this->resourceType;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function setResourceId(string $resourceId): void
    {
        $this->resourceId = $resourceId;
    }

    public function getResourceIdQueryParam(): string
    {
        return $this->resourceIdQueryParam;
    }

    public function setResourceIdQueryParam(string $resourceIdQueryParam): void
    {
        $this->resourceIdQueryParam = $resourceIdQueryParam;
    }

    public function getAuthorizationNotFoundReturnValue(): int
    {
        return $this->authorizationNotFoundReturnValue;
    }

    public function setAuthorizationNotFoundReturnValue(int $authorizationNotFoundReturnValue): void
    {
        $this->authorizationNotFoundReturnValue = $authorizationNotFoundReturnValue;
    }
}

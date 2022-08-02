<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Authorization\{
    AuthorizationInterface,
    AuthorizationQueryInterface,
    PermissionInterface,
    ResourceInterface
};
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\ResourceTypeUtil;

class AuthorizationQueryImpl extends AbstractQuery implements AuthorizationQueryInterface
{
    protected $id;
    protected $userIds = [];
    protected $groupIds = [];
    protected $resourceType;
    protected $resourceId;
    protected $permission = 0;
    protected $authorizationType;
    protected $queryByPermission = false;
    protected $queryByResourceType = false;
    private $resourcesIntersection = [];

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        if ($commandExecutor !== null) {
            parent::__construct($commandExecutor);
        }
    }

    public function authorizationId(string $id): AuthorizationQueryInterface
    {
        $this->id = $id;
        return $this;
    }

    public function userIdIn(array $userIdIn): AuthorizationQueryInterface
    {
        if (!empty($this->groupIds)) {
            throw new ProcessEngineException("Cannot query for user and group authorizations at the same time.");
        }
        $this->userIds = $userIdIn;
        return $this;
    }

    public function groupIdIn(string $groupIdIn): AuthorizationQueryInterface
    {
        if (!empty($this->userIds)) {
            throw new ProcessEngineException("Cannot query for user and group authorizations at the same time.");
        }
        $this->groupIds = $groupIdIn;
        return $this;
    }

    public function resourceType($resource): ?AuthorizationQueryInterface
    {
        if (is_int($resource)) {
            $this->resourceType = $resource;
            $this->queryByResourceType = true;
            return $this;
        } elseif ($resource instanceof ResourceInterface) {
            return $this->resourceType($resource->resourceType());
        }
        return null;
    }

    public function resourceId(string $resourceId): AuthorizationQueryInterface
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    public function hasPermission(PermissionInterface $p): AuthorizationQueryInterface
    {
        $this->queryByPermission = true;

        /*if (count($this->resourcesIntersection) == 0) {
            $this->resourcesIntersection = $p->getTypes();
        } else {*/
        $this->resourcesIntersection = $p->getTypes();
        /*}*/

        $this->permission |= $p->getValue();
        return $this;
    }

    public function authorizationType(int $type): AuthorizationQueryInterface
    {
        $this->authorizationType = $type;
        return $this;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext->getAuthorizationManager()
            ->selectAuthorizationCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext->getAuthorizationManager()
            ->selectAuthorizationByQueryCriteria($this);
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions()
            || $this->containsIncompatiblePermissions()
            || $this->containsIncompatibleResourceType();
    }

    /**
     * check whether there are any compatible resources
     * for all of the filtered permission parameters
     */
    private function containsIncompatiblePermissions(): bool
    {
        return $this->queryByPermission && empty($this->resourcesIntersection);
    }

    /**
     * check whether the permissions' resources
     * are compatible to the filtered resource parameter
     */
    private function containsIncompatibleResourceType(): bool
    {
        if ($this->queryByResourceType && $this->queryByPermission) {
            $resources = $this->resourcesIntersection;
            return !ResourceTypeUtil::resourceIsContainedInArray($this->resourceType, $resources);
        }
        return false;
    }

    // getters ////////////////////////////

    public function getId(): string
    {
        return $this->id;
    }

    public function isQueryByPermission(): bool
    {
        return $this->queryByPermission;
    }

    public function getUserIds(): array
    {
        return $this->userIds;
    }

    public function getGroupIds(): array
    {
        return $this->groupIds;
    }

    public function getResourceType(): int
    {
        return $this->resourceType;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getPermission(): int
    {
        return $this->permission;
    }

    public function isQueryByResourceType(): bool
    {
        return $this->queryByResourceType;
    }

    public function getResourcesIntersection(): array
    {
        return $this->resourcesIntersection;
    }

    public function orderByResourceType(): AuthorizationQueryInterface
    {
        $this->orderBy(AuthorizationQueryProperty::resourceType());
        return this;
    }

    public function orderByResourceId(): AuthorizationQueryInterface
    {
        $this->orderBy(AuthorizationQueryProperty::resourceId());
        return $this;
    }
}

<?php

namespace Jabe\Impl\Db;

use Jabe\ProcessEngineException;
use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Peristence\Entity\AuthorizationManager;

class PermissionCheckBuilder
{
    protected $atomicChecks = [];
    protected $compositeChecks = [];
    protected bool $disjunctive = true;

    protected $parent;

    public function __construct(?PermissionCheckBuilder $parent = null)
    {
        $this->parent = $parent;
    }

    public function disjunctive(): PermissionCheckBuilder
    {
        $this->disjunctive = true;
        return $this;
    }

    public function conjunctive(): PermissionCheckBuilder
    {
        $this->disjunctive = false;
        return $this;
    }

    public function atomicCheck(ResourceInterface $resource, ?string $queryParam, PermissionInterface $permission): PermissionCheckBuilder
    {
        if (!$this->isPermissionDisabled($permission)) {
            $permCheck = new PermissionCheck();
            $permCheck->setResource($resource);
            $permCheck->setResourceIdQueryParam($queryParam);
            $permCheck->setPermission($permission);
            $this->atomicChecks[] = $permCheck;
        }

        return $this;
    }

    public function atomicCheckForResourceId(ResourceInterface $resource, ?string $resourceId, PermissionInterface $permission): PermissionCheckBuilder
    {
        if (!$this->isPermissionDisabled($permission)) {
            $permCheck = new PermissionCheck();
            $permCheck->setResource($resource);
            $permCheck->setResourceId($resourceId);
            $permCheck->setPermission($permission);
            $this->atomicChecks[] = $permCheck;
        }

        return $this;
    }

    public function composite(): PermissionCheckBuilder
    {
        return new PermissionCheckBuilder($this);
    }

    public function done(): PermissionCheckBuilder
    {
        $this->compositeChecks[] = $this->build();
        return $this->parent;
    }

    public function build(): CompositePermissionCheck
    {
        $this->validate();

        $permissionCheck = new CompositePermissionCheck($this->disjunctive);
        $permissionCheck->setAtomicChecks($this->atomicChecks);
        $permissionCheck->setCompositeChecks($this->compositeChecks);

        return $permissionCheck;
    }

    public function getAtomicChecks(): array
    {
        return $this->atomicChecks;
    }

    protected function validate(): void
    {
        if (!empty($this->atomicChecks) && !empty($this->compositeChecks)) {
            throw new ProcessEngineException("Mixed authorization checks of atomic and composite permissions are not supported");
        }
    }

    public function isPermissionDisabled(PermissionInterface $permission): bool
    {
        $authorizationManager = Context::getCommandContext()->getAuthorizationManager();
        return $authorizationManager->isPermissionDisabled($permission);
    }
}

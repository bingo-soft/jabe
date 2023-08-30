<?php

namespace Jabe\Impl\Db;

class CompositePermissionCheck
{
    protected $disjunctive;

    protected $compositeChecks = [];

    protected $atomicChecks = [];

    public function __construct(bool $disjunctive = true)
    {
        $this->disjunctive = $disjunctive;
    }

    public function addAtomicCheck(PermissionCheck $permissionCheck): void
    {
        $this->atomicChecks[] = $permissionCheck;
    }

    public function setAtomicChecks(array $atomicChecks): void
    {
        $this->atomicChecks = $atomicChecks;
    }

    public function setCompositeChecks(array $subChecks): void
    {
        $this->compositeChecks = $subChecks;
    }

    public function addCompositeCheck(CompositePermissionCheck $subCheck): void
    {
        $this->compositeChecks[] = $subCheck;
    }

    /**
     * conjunctive else
     */
    public function isDisjunctive(): bool
    {
        return $this->disjunctive;
    }

    public function getCompositeChecks(): array
    {
        return $this->compositeChecks;
    }

    public function getAtomicChecks(): array
    {
        return $this->atomicChecks;
    }

    public function clear(): void
    {
        $this->compositeChecks = [];
        $this->atomicChecks = [];
    }

    public function getAllPermissionChecks(): array
    {
        $allChecks = [];

        $allChecks = array_merge([], $this->atomicChecks);

        foreach ($this->compositeChecks as $compositePermissionCheck) {
            $allChecks = array_merge($allChecks, $compositePermissionCheck->getAllPermissionChecks());
        }

        return $allChecks;
    }
}

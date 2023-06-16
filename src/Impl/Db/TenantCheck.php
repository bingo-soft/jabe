<?php

namespace Jabe\Impl\Db;

class TenantCheck
{
    /**
     * If <code>true</code> then the process engine performs tenant checks to
     * ensure that the query only access data that belongs to one of the
     * authenticated tenant ids.
     */
    protected bool $isTenantCheckEnabled = true;

    /** the ids of the authenticated tenants */
    protected $authTenantIds = [];

    public function __serialize(): array
    {
        return [
            'isTenantCheckEnabled' => $this->isTenantCheckEnabled,
            'authTenantIds' => $this->authTenantIds
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->isTenantCheckEnabled = $data['isTenantCheckEnabled'];
        $this->authTenantIds = $data['authTenantIds'];
    }

    public function isTenantCheckEnabled(): bool
    {
        return $this->isTenantCheckEnabled;
    }

    /** is used by myBatis */
    public function getIsTenantCheckEnabled(): bool
    {
        return $this->isTenantCheckEnabled;
    }

    public function setTenantCheckEnabled(bool $isTenantCheckEnabled): void
    {
        $this->isTenantCheckEnabled = $isTenantCheckEnabled;
    }

    public function getAuthTenantIds(): ?array
    {
        return $this->authTenantIds;
    }

    public function setAuthTenantIds(?array $tenantIds = []): void
    {
        $this->authTenantIds = $tenantIds;
    }
}

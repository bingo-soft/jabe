<?php

namespace Jabe\Engine\Impl\Identity;

class Authentication
{
    protected $authenticatedUserId;
    protected $authenticatedGroupIds;
    protected $authenticatedTenantIds;

    public function __construct(
        string $authenticatedUserId,
        ?array $authenticatedGroupIds = null,
        ?array $authenticatedTenantIds = null
    ) {
        $this->authenticatedUserId = $authenticatedUserId;

        if ($authenticatedGroupIds != null) {
            $this->authenticatedGroupIds = $authenticatedGroupIds;
        }

        if ($authenticatedTenantIds != null) {
            $this->authenticatedTenantIds = $authenticatedTenantIds;
        }
    }

    public function getGroupIds(): ?array
    {
        return $this->authenticatedGroupIds;
    }

    public function getUserId(): string
    {
        return $this->authenticatedUserId;
    }

    public function getTenantIds(): ?array
    {
        return $this->authenticatedTenantIds;
    }
}

<?php

namespace Jabe\Identity;

use Jabe\Query\QueryInterface;

interface TenantQueryInterface extends QueryInterface
{
    public function tenantId(string $tenantId): TenantQueryInterface;

    public function tenantIdIn(array $ids): TenantQueryInterface;

    public function tenantName(string $tenantName): TenantQueryInterface;

    public function tenantNameLike(string $tenantNameLike): TenantQueryInterface;

    public function userMember(string $userId): TenantQueryInterface;

    public function groupMember(string $groupId): TenantQueryInterface;

    public function includingGroups(string $groupId): TenantQueryInterface;

    public function orderByTenantId(): TenantQueryInterface;

    public function orderByTenantName(): TenantQueryInterface;
}

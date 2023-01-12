<?php

namespace Jabe\Identity;

interface GroupQueryInterface
{
    public function groupId(?string $groupId): GroupQueryInterface;

    public function groupIdIn(array $ids): GroupQueryInterface;

    public function groupName(?string $groupName): GroupQueryInterface;

    public function groupNameLink(?string $groupNameLink): GroupQueryInterface;

    public function groupType(?string $groupType): GroupQueryInterface;

    public function groupMember(?string $groupMemberUserId): GroupQueryInterface;

    public function potentialStarter(?string $procDefId): GroupQueryInterface;

    public function memberOfTenant(?string $tenantId): GroupQueryInterface;

    public function orderByGroupId(): GroupQueryInterface;

    public function orderByGroupName(): GroupQueryInterface;

    public function orderByGroupType(): GroupQueryInterface;
}

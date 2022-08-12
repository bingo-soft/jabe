<?php

namespace Jabe\Identity;

use Jabe\Query\QueryInterface;

interface UserQueryInterface extends QueryInterface
{
    public function userId(string $userId): UserQueryInterface;

    public function userIdIn(array $ids): UserQueryInterface;

    public function userFirstName(string $firstName): UserQueryInterface;

    public function userFirstNameLike(string $firstNameLike): UserQueryInterface;

    public function userLastName(string $lastName): UserQueryInterface;

    public function userLastNameLike(string $lastNameLike): UserQueryInterface;

    public function userEmail(string $email): UserQueryInterface;

    public function userEmailLike(string $emailLike): UserQueryInterface;

    public function memberOfGroup(string $groupId): UserQueryInterface;

    public function potentialStarter(string $procDefId): UserQueryInterface;

    public function memberOfTenant(string $tenantId): UserQueryInterface;

    public function orderByUserId(): UserQueryInterface;

    public function orderByUserFirstName(): UserQueryInterface;

    public function orderByUserLastName(): UserQueryInterface;

    public function orderByUserEmail(): UserQueryInterface;
}

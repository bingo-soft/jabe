<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Persistence\AbstractManager;

class IdentityInfoManager extends AbstractManager
{
    public function deleteUserInfoByUserIdAndKey(string $userId, string $key): void
    {
        $identityInfoEntity = $this->findUserInfoByUserIdAndKey($userId, $key);
        if ($identityInfoEntity != null) {
            $this->deleteIdentityInfo($identityInfoEntity);
        }
    }

    public function deleteIdentityInfo(IdentityInfoEntity $identityInfo): void
    {
        $this->getDbEntityManager()->delete($identityInfo);
        if (IdentityInfoEntity::TYPE_USERACCOUNT == $identityInfo->getType()) {
            foreach ($this->findIdentityInfoDetails($identityInfo->getId()) as $identityInfoDetail) {
                $this->getDbEntityManager()->delete($identityInfoDetail);
            }
        }
    }

    public function findUserAccountByUserIdAndKey(string $userId, string $userPassword, string $key): ?IdentityInfoEntity
    {
        $identityInfoEntity = $this->findUserInfoByUserIdAndKey($userId, $key);
        if ($identityInfoEntity == null) {
            return null;
        }

        $details = [];
        $identityInfoId = $identityInfoEntity->getId();
        $identityInfoDetails = $this->findIdentityInfoDetails($identityInfoId);
        foreach ($identityInfoDetails as $identityInfoDetail) {
            $details[$identityInfoDetail->getKey()] = $identityInfoDetail->getValue();
        }
        $identityInfoEntity->setDetails($details);

        if ($identityInfoEntity->getPasswordBytes() != null) {
            $password = $this->decryptPassword($identityInfoEntity->getPasswordBytes(), $userPassword);
            $identityInfoEntity->setPassword($password);
        }

        return $identityInfoEntity;
    }

    protected function findIdentityInfoDetails(string $identityInfoId): array
    {
        return $this->getDbEntityManager()->selectList("selectIdentityInfoDetails", $identityInfoId);
    }

    public function setUserInfo(string $userId, string $userPassword, string $type, string $key, string $value, string $accountPassword, array $accountDetails): void
    {
        $storedPassword = null;
        if ($accountPassword != null) {
            $storedPassword = $this->encryptPassword($accountPassword, $userPassword);
        }

        $identityInfoEntity = $this->findUserInfoByUserIdAndKey($userId, $key);
        if ($identityInfoEntity != null) {
            // update
            $identityInfoEntity->setValue($value);
            $identityInfoEntity->setPasswordBytes($storedPassword);

            if ($accountDetails == null) {
                $accountDetails = [];
            }

            $newKeys = array_keys($accountDetails);
            $identityInfoDetails = $this->findIdentityInfoDetails($identityInfoEntity->getId());
            foreach ($identityInfoDetails as $identityInfoDetail) {
                $detailKey = $identityInfoDetail->getKey();
                foreach ($newKeys as $key => $val) {
                    if ($val == $detailKey) {
                        unset($newKeys[$key]);
                    }
                }
                $newDetailValue = null;
                if (array_key_exists($detailKey, $accountDetails)) {
                    $newDetailValue = $accountDetails[$detailKey];
                }
                if ($newDetailValue == null) {
                    $this->deleteIdentityInfo($identityInfoDetail);
                } else {
                    // update detail
                    $identityInfoDetail->setValue($newDetailValue);
                }
            }
            $this->insertAccountDetails($identityInfoEntity, $accountDetails, $newKeys);
        } else {
            // insert
            $identityInfoEntity = new IdentityInfoEntity();
            $identityInfoEntity->setUserId($userId);
            $identityInfoEntity->setType($type);
            $identityInfoEntity->setKey($key);
            $identityInfoEntity->setValue($value);
            $identityInfoEntity->setPasswordBytes($storedPassword);
            $this->getDbEntityManager()->insert($identityInfoEntity);
            if ($accountDetails != null) {
                $this->insertAccountDetails($identityInfoEntity, $accountDetails, array_keys($accountDetails));
            }
        }
    }

    private function insertAccountDetails(IdentityInfoEntity $identityInfoEntity, array $accountDetails, array $keys): void
    {
        foreach ($keys as $newKey) {
            // insert detail
            $identityInfoDetail = new IdentityInfoEntity();
            $identityInfoDetail->setParentId($identityInfoEntity->getId());
            $identityInfoDetail->setKey($newKey);
            $identityInfoDetail->setValue($accountDetails[$newKey]);
            $this->getDbEntityManager()->insert($identityInfoDetail);
        }
    }

    public function encryptPassword(string $accountPassword, string $userPassword): string
    {
        // TODO
        return $accountPassword;
    }

    public function decryptPassword(string $storedPassword, string $userPassword): string
    {
        // TODO
        return $storedPassword;
    }

    public function findUserInfoByUserIdAndKey(string $userId, string $key): ?IdentityInfoEntity
    {
        $parameters = [];
        $parameters["userId"] = $userId;
        $parameters["key"] = $key;
        return $this->getDbEntityManager()->selectOne("selectIdentityInfoByUserIdAndKey", $parameters);
    }

    public function findUserInfoKeysByUserIdAndType(string $userId, string $type): array
    {
        $parameters = [];
        $parameters["userId"] = $userId;
        $parameters["type"] = $type;
        return $this->getDbEntityManager()->selectList("selectIdentityInfoKeysByUserIdAndType", $parameters);
    }

    public function deleteUserInfoByUserId(string $userId): void
    {
        $identityInfos = $this->getDbEntityManager()->selectList("selectIdentityInfoByUserId", $userId);
        foreach ($identityInfos as $identityInfo) {
            $this->getIdentityInfoManager()->deleteIdentityInfo($identityInfo);
        }
    }

    public function updateUserLock(UserEntity $user, int $attempts, string $lockExpirationTime): void
    {
        $user->setAttempts($attempts);
        $user->setLockExpirationTime($lockExpirationTime);
        $this->getDbEntityManager()->update(UserEntity::class, "updateUserLock", $user);
    }
}

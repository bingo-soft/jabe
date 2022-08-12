<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Identity\{
    PasswordPolicyResultInterface,
    UserInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    HasDbRevisionInterface,
    DbEntityInterface
};
use Jabe\Impl\Util\ClassNameUtil;

class UserEntity implements UserInterface, \Serializable, DbEntityInterface, HasDbRevisionInterface
{
    protected $id;
    protected $revision;
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $password;
    protected $newPassword;
    protected $salt;
    protected $lockExpirationTime;
    protected $attempts;

    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["firstName"] = $this->firstName;
        $persistentState["lastName"] = $this->lastName;
        $persistentState["email"] = $this->email;
        $persistentState["password"] = $this->password;
        $persistentState["salt"] = $this->salt;
        return $persistentState;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->newPassword = $password;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    /**
     * Special setter for MyBatis.
     */
    public function setDbPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getLockExpirationTime(): string
    {
        return $this->lockExpirationTime;
    }

    public function setLockExpirationTime(string $lockExpirationTime): void
    {
        $this->lockExpirationTime = $lockExpirationTime;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts): void
    {
        $this->attempts = $attempts;
    }

    public function encryptPassword(?string $password = null, ?string $salt = null)
    {
        if ($password === null && $salt === null) {
            if ($this->newPassword !== null) {
                $this->salt = $this->generateSalt();
                $this->setDbPassword($this->encryptPassword($this->newPassword, $this->salt));
            }
        } elseif ($password === null) {
            return null;
        } else {
            $saltedPassword = $this->saltPassword($password, $salt);
            return Context::getProcessEngineConfiguration()
            ->getPasswordManager()
            ->encrypt($saltedPassword);
        }
    }

    protected function generateSalt(): string
    {
        return Context::getProcessEngineConfiguration()
            ->getSaltGenerator()
            ->generateSalt();
    }

    public function checkPasswordAgainstPolicy(): bool
    {
        $result = Context::getProcessEngineConfiguration()
            ->getIdentityService()
            ->checkPasswordAgainstPolicy($this->newPassword, $this);

        return $result->isValid();
    }

    public function hasNewPassword(): bool
    {
        return $this->newPassword !== null;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'revision' => $this->revision,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'password' => $this->password,
            'salt' => $this->salt,
            'lockExpirationTime' => $this->lockExpirationTime,
            'attempts' => $this->attempts
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->revision = $json->revision;
        $this->firstName = $json->firstName;
        $this->lastName = $json->lastName;
        $this->email = $json->email;
        $this->password = $json->password;
        $this->salt = $json->salt;
        $this->lockExpirationTime = $json->lockExpirationTime;
        $this->attempts = $json->attempts;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", revision=" . $this->revision
                . ", firstName=" . $this->firstName
                . ", lastName=" . $this->lastName
                . ", email=" . $this->email
                . ", password=" . $this->password
                . ", salt=" . $this->salt
                . ", lockExpirationTime=" . $this->lockExpirationTime
                . ", attempts=" . $this->attempts
                . "]";
    }
}

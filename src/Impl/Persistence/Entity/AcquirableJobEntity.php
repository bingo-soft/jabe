<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\{
    DbEntityInterface,
    HasDbRevisionInterface
};
use Jabe\Impl\Util\ClassNameUtil;

class AcquirableJobEntity implements DbEntityInterface, HasDbRevisionInterface
{
    public const DEFAULT_EXCLUSIVE = true;

    protected $id;
    protected int $revision = 0;

    protected $lockOwner = null;
    protected $lockExpirationTime = null;
    protected ?string $duedate = null;

    protected $processInstanceId = null;

    protected $isExclusive = self::DEFAULT_EXCLUSIVE;

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["lockOwner"] = $this->lockOwner;
        $persistentState["lockExpirationTime"] = $this->lockExpirationTime;
        $persistentState["duedate"] = $this->duedate;
        return $persistentState;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getDuedate(): ?string
    {
        return $this->duedate;
    }

    public function setDuedate(/*?string|\DateTime*/$duedate = null): void
    {
        $this->duedate = $duedate instanceof \DateTime ? $duedate->format('Y-m-d H:i:s') : $duedate;
    }

    public function getLockOwner(): ?string
    {
        return $this->lockOwner;
    }

    public function setLockOwner(?string $lockOwner): void
    {
        $this->lockOwner = $lockOwner;
    }

    public function getLockExpirationTime(): ?string
    {
        return $this->lockExpirationTime;
    }

    public function setLockExpirationTime(?string $lockExpirationTime): void
    {
        $this->lockExpirationTime = $lockExpirationTime;
    }

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(?string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function isExclusive(): bool
    {
        return $this->isExclusive;
    }

    public function setExclusive(bool $isExclusive): void
    {
        $this->isExclusive = $isExclusive;
    }


    public function equals($obj = null): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj === null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->id === null) {
            if ($obj->id !== null) {
                return false;
            }
        } elseif ($this->id != $obj->id) {
            return false;
        }
        return true;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
            . "[id=" . $this->id
            . ", revision=" . $this->revision
            . ", lockOwner=" . $this->lockOwner
            . ", lockExpirationTime=" . $this->lockExpirationTime
            . ", duedate=" . $this->duedate
            . ", processInstanceId=" . $this->processInstanceId
            . ", isExclusive=" . $this->isExclusive
            . "]";
    }
}

<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\DbEntityInterface;
use Jabe\Management\SchemaLogEntryInterface;
use Jabe\Impl\Util\ClassNameUtil;

class SchemaLogEntryEntity implements SchemaLogEntryInterface, DbEntityInterface
{
    protected $id;
    protected $timestamp;
    protected $version;

    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    public function setTimestamp(?string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    // persistent object methods ////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["id"] = $this->id;
        $persistentState["timestamp"] = $this->timestamp;
        $persistentState["version"] = $this->version;
        return $persistentState;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'value' => $this->value
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->timestamp = $data['timestamp'];
        $this->version = $data['version'];
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
            . "[id=" . $this->id
            . ", timestamp=" . $this->timestamp
            . ", version=" . $this->version
            . "]";
    }
}

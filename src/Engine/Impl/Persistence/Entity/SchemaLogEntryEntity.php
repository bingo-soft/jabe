<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Db\DbEntityInterface;
use BpmPlatform\Engine\Management\SchemaLogEntryInterface;
use BpmPlatform\Engine\Impl\Util\ClassNameUtil;

class SchemaLogEntryEntity implements SchemaLogEntryInterface, DbEntityInterface, \Serializable
{
    protected $id;
    protected $timestamp;
    protected $version;

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function setTimestamp(string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    // persistent object methods ////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
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

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'value' => $this->value
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->timestamp = $json->timestamp;
        $this->version = $json->version;
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

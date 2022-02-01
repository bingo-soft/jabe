<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Db\{
    DbEntityInterface,
    HasDbReferencesInterface
};

class MeterLogEntity implements DbEntityInterface, HasDbReferencesInterface, \Serializable
{
    protected $id;

    protected $timestamp;
    protected $milliseconds;

    protected $name;

    protected $reporter;

    protected $value;

    public function __construct(string $name, ?string $reporter, int $value, string $timestamp)
    {
        $this->name = $name;
        $this->reporter = $reporter;
        $this->value = $value;
        $this->timestamp = $timestamp;
        $this->milliseconds = intval($timestamp) * 1000;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'name' => $this->name,
            'reporter' => $this->reporter,
            'value' => $this->value,
            'timestamp' => $this->timestamp,
            'milliseconds' => $this->milliseconds,
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->name = $json->name;
        $this->reporter = $json->reporter;
        $this->value = $json->value;
        $this->timestamp = $json->timestamp;
        $this->milliseconds = $json->milliseconds;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function setTimestamp(string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getMilliseconds(): int
    {
        return $this->milliseconds;
    }

    public function setMilliseconds(int $milliseconds): void
    {
        $this->milliseconds = $milliseconds;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getReporter(): string
    {
        return $this->reporter;
    }

    public function setReporter(string $reporter): void
    {
        $this->reporter = $reporter;
    }

    public function getPersistentState()
    {
        // immutable
        return (new \ReflectionClass($this));
    }

    public function getReferencedEntityIds(): array
    {
        $referencedEntityIds = [];
        return $referencedEntityIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];
        return $referenceIdAndClass;
    }
}

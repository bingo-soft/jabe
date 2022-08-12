<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\{
    DbEntityInterface,
    HasDbReferencesInterface
};

class TaskMeterLogEntity implements DbEntityInterface, HasDbReferencesInterface, \Serializable
{
    protected $id;

    protected $timestamp;

    protected $assigneeHash;

    public function __construct(?string $assignee = null, ?string $timestamp = null)
    {
        if ($assignee !== null && $timestamp !== null) {
            $this->assigneeHash = $this->createHashAsLong($assignee);
            $this->timestamp = $timestamp;
        }
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'assigneeHash' => $this->assigneeHash
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->timestamp = $json->timestamp;
        $this->assigneeHash = $json->assigneeHash;
    }

    protected function createHashAsLong(string $assignee): int
    {
        $res = base64_encode(md5($assignee));
        $ar = unpack("C*", $res);
        return ($ar[1] << 24) + ($ar[2] << 16) + ($ar[3] << 8) + $ar[4];
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

    public function getAssigneeHash(): int
    {
        return $this->assigneeHash;
    }

    public function setAssigneeHash(int $assigneeHash): void
    {
        $this->assigneeHash = $assigneeHash;
    }

    public function getPersistentState()
    {
        // immutable
        return new \ReflectionClass($this);
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

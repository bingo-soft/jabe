<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Identity\TenantInterface;
use Jabe\Impl\Db\{
    DbEntityInterface,
    HasDbRevisionInterface
};

class TenantEntity implements TenantInterface, DbEntityInterface, HasDbRevisionInterface
{
    protected $id;
    protected $name;

    protected int $revision = 0;

    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["name"] = $this->name;
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

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'revision' => $this->revision
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->revision = $data['revision'];
    }

    public function __toString()
    {
        return "TenantEntity [id=" . $this->id . ", name=" . $this->name . ", revision=" . $this->revision . "]";
    }
}

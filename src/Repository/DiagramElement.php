<?php

namespace Jabe\Repository;

abstract class DiagramElement
{
    protected $id = null;

    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    /**
     * Id of the diagram element.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return "id=" . $this->getId();
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
    }

    abstract public function isNode(): bool;
    abstract public function isEdge(): bool;
}

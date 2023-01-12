<?php

namespace Jabe\Repository;

abstract class DiagramElement implements \Serializable
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

    public function serialize()
    {
        return json_encode([
            'id' => $this->id
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
    }

    abstract public function isNode(): bool;
    abstract public function isEdge(): bool;
}

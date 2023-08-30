<?php

namespace Jabe\Repository;

class DiagramEdge extends DiagramElement
{
    private $waypoints;

    public function __construct(?string $id, array $waypoints)
    {
        parent::__construct($id);
        $this->waypoints = $waypoints;
    }

    public function isNode(): bool
    {
        return false;
    }

    public function isEdge(): bool
    {
        return true;
    }

    public function getWaypoints(): array
    {
        return $this->waypoints;
    }

    public function setWaypoints(array $waypoints): void
    {
        $this->waypoints = $waypoints;
    }
}

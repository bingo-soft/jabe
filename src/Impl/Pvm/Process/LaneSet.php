<?php

namespace Jabe\Impl\Pvm\Process;

class LaneSet
{
    protected $id;
    protected $lanes = [];
    protected $name;

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getLanes(): array
    {
        return $this->lanes;
    }

    public function addLane(Lane $laneToAdd): void
    {
        $this->lanes[] = $laneToAdd;
    }

    public function getLaneForId(?string $id): ?Lane
    {
        foreach ($this->lanes as $lane) {
            if ($id == $lane->getId()) {
                return $lane;
            }
        }
        return null;
    }
}

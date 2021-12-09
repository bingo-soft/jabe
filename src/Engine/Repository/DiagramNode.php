<?php

namespace BpmPlatform\Engine\Repository;

class DiagramNode extends DiagramElement
{
    private $x = null;
    private $y = null;
    private $width = null;
    private $height = null;

    public function __construct(?string $id = null, ?float $x = null, ?float $y = null, ?float $width = null, ?float $height = null)
    {
        parent::__construct($id);
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    public function getX(): ?float
    {
        return $this->x;
    }

    public function setX(float $x): void
    {
        $this->x = $x;
    }

    public function getY(): ?float
    {
        return $this->y;
    }

    public function setY(float $y): void
    {
        $this->y = $y;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    public function __toString()
    {
        return parent::__toString() . ", x=" . $this->x . ", y=" . $this->y . ", width=" . $this->width . ", height=" . $this->height;
    }

    public function isNode(): bool
    {
        return true;
    }

    public function isEdge(): bool
    {
        return false;
    }
}

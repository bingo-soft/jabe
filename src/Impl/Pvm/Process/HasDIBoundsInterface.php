<?php

namespace Jabe\Impl\Pvm\Process;

interface HasDIBoundsInterface
{
    public function getWidth(): int;
    public function getHeight(): int;
    public function getX(): int;
    public function getY(): int;

    public function setWidth(int $width): void;
    public function setHeight(int $height): void;
    public function setX(int $x): void;
    public function setY(int $y): void;
}

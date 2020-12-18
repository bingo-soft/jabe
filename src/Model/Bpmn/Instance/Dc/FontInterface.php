<?php

namespace BpmPlatform\Model\Bpmn\Instance\Dc;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface FontInterface extends BpmnModelElementInstanceInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getSize(): float;

    public function setSize(float $size): void;

    public function isBold(): bool;

    public function setBold(bool $isBold): void;

    public function isItalic(): bool;

    public function setItalic(bool $isItalic): void;

    public function isUnderline(): bool;

    public function setUnderline(bool $isUnderline): void;

    public function isStrikeThrough(): bool;

    public function setStrikeTrough(bool $isStrikeTrough): void;
}

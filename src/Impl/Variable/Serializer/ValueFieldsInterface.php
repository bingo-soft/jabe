<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Impl\Persistence\Entity\NameableInterface;

interface ValueFieldsInterface extends NameableInterface
{
    public function getTextValue(): ?string;
    public function setTextValue(?string $textValue): void;

    public function getTextValue2(): ?string;
    public function setTextValue2(?string $textValue2): void;

    public function getLongValue(): ?int;
    public function setLongValue(int $longValue): void;

    public function getDoubleValue(): ?float;
    public function setDoubleValue(float $doubleValue): void;

    public function getByteArrayValue(): ?string;
    public function setByteArrayValue($bytes): void;
}

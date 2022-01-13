<?php

namespace BpmPlatform\Engine\Impl\Variable\Serializer;

use BpmPlatform\Engine\Impl\Persistence\Entity\NameableInterface;

interface ValueFieldsInterface extends NameableInterface
{
    public function getTextValue(): ?string;
    public function setTextValue(string $textValue): void;

    public function getTextValue2(): ?string;
    public function setTextValue2(string $textValue2): void;

    public function getIntValue(): ?int;
    public function setIntValue(int $longValue): void;

    public function getDoubleValue(): ?float;
    public function setDoubleValue(floar $doubleValue): void;

    public function getByteArrayValue(): ?string;
    public function setByteArrayValue(string $bytes): void;
}

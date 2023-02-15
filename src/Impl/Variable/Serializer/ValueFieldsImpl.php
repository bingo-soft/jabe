<?php

namespace Jabe\Impl\Variable\Serializer;

class ValueFieldsImpl implements ValueFieldsInterface
{
    protected $text;
    protected $text2;
    protected $intValue;
    protected $doubleValue;
    protected $byteArrayValue;

    public function getName(): ?string
    {
        return null;
    }

    public function getTextValue(): ?string
    {
        return $this->text;
    }

    public function setTextValue(?string $textValue): void
    {
        $this->text = $textValue;
    }

    public function getTextValue2(): ?string
    {
        return $this->text2;
    }

    public function setTextValue2(?string $textValue2): void
    {
        $this->text2 = $textValue2;
    }

    public function getLongValue(): ?int
    {
        return $this->intValue;
    }

    public function setLongValue(?int $intValue): void
    {
        $this->intValue = $intValue;
    }

    public function getDoubleValue(): ?float
    {
        return $this->doubleValue;
    }

    public function setDoubleValue(float $doubleValue): void
    {
        $this->doubleValue = $doubleValue;
    }

    public function getByteArrayValue(): ?string
    {
        return $this->byteArrayValue;
    }

    public function setByteArrayValue($bytes): void
    {
        $this->byteArrayValue = $bytes;
    }
}

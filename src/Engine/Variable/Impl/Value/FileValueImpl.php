<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Type\{
    FileValueTypeInterface,
    ValueTypeInterface
};
use BpmPlatform\Engine\Variable\Value\FileValueInterface;

class FileValueImpl implements FileValueInterface
{
    protected $mimeType;
    protected $filename;
    protected $value;
    protected $type;
    protected $encoding;
    protected $isTransient;

    public function __construct(
        ?string $value,
        FileValueTypeInterface $type,
        string $filename,
        ?string $mimeType,
        ?string $encoding
    ) {
        $this->value = $value;
        $this->type = $type;
        $this->filename = $filename;
        $this->mimeType = $mimeType;
        $this->encoding = $encoding;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): ValueTypeInterface
    {
        return $this->type;
    }

    public function setEncoding(string $encoding): void
    {
        $this->encoding = $encoding;
    }

    public function getEncodingAsCharset(): string
    {
        return $this->encoding;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function __toString()
    {
        return "FileValueImpl [mimeType=" . $this->mimeType . ", filename=" . $this->filename
               . ", type=" . $this->type . ", isTransient=" . $this->isTransient . "]";
    }

    public function isTransient(): bool
    {
        return $this->isTransient;
    }

    public function setTransient(bool $isTransient): void
    {
        $this->isTransient = $isTransient;
    }
}

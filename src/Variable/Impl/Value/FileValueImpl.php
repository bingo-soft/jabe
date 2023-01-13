<?php

namespace Jabe\Variable\Impl\Value;

use Jabe\Variable\Type\{
    FileValueTypeInterface,
    ValueTypeInterface
};
use Jabe\Variable\Value\FileValueInterface;

class FileValueImpl implements FileValueInterface
{
    protected $mimeType;
    protected $filename;
    protected $value;
    protected $type;
    protected $encoding;
    protected bool $isTransient = false;

    public function __construct(
        FileValueTypeInterface $type,
        ?string $filename,
        ?string $mimeType = null,
        ?string $encoding = null,
        ?string $value = null
    ) {
        $this->type = $type;
        $this->filename = $filename;
        $this->mimeType = $mimeType;
        $this->encoding = $encoding;
        $this->value = $value;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function getValue()
    {
        if (file_exists($this->filename)) {
            $file = fopen($this->filename, 'r+');
        } else {
            $file = tmpfile();
            fwrite($file, $this->value);
        }
        return $file;
    }

    public function getType(): ValueTypeInterface
    {
        return $this->type;
    }

    public function setEncoding(?string $encoding): void
    {
        $this->encoding = $encoding;
    }

    public function getEncodingAsCharset(): ?string
    {
        return $this->encoding;
    }

    public function getEncoding(): ?string
    {
        return $this->encoding;
    }

    public function getByteArray(): ?string
    {
        return $this->value;
    }

    public function __toString()
    {
        return "FileValueImpl [mimeType=" . $this->mimeType . ", filename=" . $this->filename
               . ", type=" . $this->type . ", isTransient=" . $this->isTransient . "]";
    }

    public function serialize()
    {
        return json_encode([
            'mimeType' => $this->mimeType,
            'filename' => $this->filename,
            'type' => serialize($this->type),
            'isTransient' => $this->isTransient
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->mimeType = $json->mimeType;
        $this->filename = $json->filename;
        $this->type = unserialize($json->type);
        $this->isTransient = $json->isTransient;
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

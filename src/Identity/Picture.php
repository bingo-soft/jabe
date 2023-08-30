<?php

namespace Jabe\Identity;

class Picture
{
    protected $bytes;
    protected $mimeType;

    public function __construct(?string $bytes, ?string $mimeType)
    {
        $this->bytes = $bytes;
        $this->mimeType = $mimeType;
    }

    public function __serialize(): array
    {
        return [
            'bytes' => $this->bytes,
            'mimeType' => $this->mimeType
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->bytes = $data['bytes'];
        $this->mimeType = $data['mimeType'];
    }

    public function getBytes(): ?string
    {
        return $this->bytes;
    }

    public function getInputStream(): ?string
    {
        return $this->bytes;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }
}

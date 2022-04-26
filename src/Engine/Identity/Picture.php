<?php

namespace Jabe\Engine\Identity;

class Picture implements \Serializable
{
    protected $bytes;
    protected $mimeType;

    public function __construct(string $bytes, string $mimeType)
    {
        $this->bytes = $bytes;
        $this->mimeType = $mimeType;
    }

    public function serialize()
    {
        return json_encode([
            'bytes' => $this->bytes,
            'mimeType' => $this->mimeType
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->bytes = $json->bytes;
        $this->mimeType = $json->mimeType;
    }

    public function getBytes(): string
    {
        return $this->bytes;
    }

    public function getInputStream(): string
    {
        return $this->bytes;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }
}

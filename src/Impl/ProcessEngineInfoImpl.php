<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineInfoInterface;

class ProcessEngineInfoImpl implements \Serializable, ProcessEngineInfoInterface
{
    private $name;
    private $resourceUrl;
    private $exception;

    public function __construct(?string $name, ?string $resourceUrl, ?string $exception)
    {
        $this->name = $name;
        $this->resourceUrl = $resourceUrl;
        $this->exception = $exception;
    }

    public function serialize()
    {
        return json_encode([
            'name' => $this->name,
            'resourceUrl' => $this->resourceUrl,
            'exception' => $this->exception
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->name = $json->name;
        $this->resourceUrl = $json->resourceUrl;
        $this->exception = $json->exception;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getResourceUrl(): ?string
    {
        return $this->resourceUrl;
    }

    public function getException(): ?string
    {
        return $this->exception;
    }
}

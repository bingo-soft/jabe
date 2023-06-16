<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineInfoInterface;

class ProcessEngineInfoImpl implements ProcessEngineInfoInterface
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

    public function __serialize(): array
    {
        return [
            'name' => $this->name,
            'resourceUrl' => $this->resourceUrl,
            'exception' => $this->exception
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->resourceUrl = $data['resourceUrl'];
        $this->exception = $data['exception'];
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
